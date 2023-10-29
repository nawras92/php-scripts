<?php

// Include Database Configuration
include_once '../db-config.php';
// Include the articles validator
include_once '../utils/ApiValidator.php';
// Include the articles sanitizer
include_once '../utils/ApiSanitizer.php';
// Include utils
include_once '../utils/is-authorized.php';

// HTTP Method
$method = $_SERVER['REQUEST_METHOD'];

// GET a user
if ($method === 'GET') {
  if (isset($_GET['id'])) {
    // Validate ID
    if (is_numeric($_GET['id']) && $_GET['id'] > 0) {
      $userId = $_GET['id'];
      try {
        $sql_query = "SELECT * FROM `nx_users` WHERE id = $userId";
        $result = $conn->query($sql_query);
        if ($result->num_rows === 1) {
          $user = $result->fetch_assoc();
          http_response_code(200);
          echo json_encode([
            'status' => 'success',
            'data' => $user,
            'message' => 'Operation Completed',
            'error' => null,
          ]);
          return;
        } else {
          http_response_code(404);
          echo json_encode([
            'status' => 'error',
            'data' => null,
            'message' => 'Record Not Found',
            'error' => [
              'code' => 404,
              'message' => 'The requested user is not found',
            ],
          ]);
          return;
        }
      } catch (Exception $error) {
        http_response_code(500);
        echo json_encode([
          'status' => 'error',
          'data' => null,
          'message' => 'Server Error',
          'error' => [
            'code' => 500,
            'message' => $error,
          ],
        ]);
        return;
      }
    } else {
      http_response_code(400);
      echo json_encode([
        'status' => 'error',
        'data' => null,
        'message' => 'Invalid ID',
        'error' => [
          'code' => 400,
          'message' => 'The provided user id is not a valid integer',
        ],
      ]);
      return;
    }
  } else {
    http_response_code(400);
    echo json_encode([
      'status' => 'error',
      'data' => null,
      'message' => 'No ID Provided',
      'error' => ['code' => 400, 'message' => 'The user ID is NOT provided'],
    ]);
    return;
  }
}

// ADD user
if ($method === 'POST') {
  try {
    $authorized = is_super_admin();
    if (!$authorized['authorized']) {
      http_response_code(401);
      echo json_encode([
        'status' => 'error',
        'data' => 'null',
        'message' => 'Unauthorized',
        'error' => [
          'code' => 401,
          'message' => $authorized['message'],
        ],
      ]);
      return;
    }
    // The user is super admin
    // get data
    $data = json_decode(file_get_contents('php://input'), true);
    // email/password/role/firstName/lastName
    // Sanitize Data
    $data = APISanitizer::sanitizeUser($data);
    $errors = APIValidator::validateUser($data);
    if (!empty($errors)) {
      http_response_code(422);
      echo json_encode([
        'status' => 'error',
        'data' => null,
        'message' => 'Validation Error',
        'error' => [
          'code' => 422,
          'message' => $errors,
        ],
      ]);
      return;
    }
    // No Validation Errors
    extract($data); // $email/$lastName.....
    // User Email must be unique
    $query_user = "SELECT * FROM `nx_users` WHERE `email` = '$email'";
    $result = $conn->query($query_user);
    if ($result->num_rows > 0) {
      http_response_code(409);
      echo json_encode([
        'status' => 'error',
        'data' => null,
        'message' => 'Duplicate Email',
        'error' => [
          'code' => 409,
          'message' => 'The user email already exists in the database',
        ],
      ]);
      return;
    }
    // Hash the password
    $password = password_hash($data['password'], PASSWORD_DEFAULT);
    $data['password'] = $password;
    // Process the user data
    $columnNames = array_keys($data);
    $columnValues = array_values($data);
    $query_sql =
      'INSERT INTO `nx_users` (' .
      implode(', ', $columnNames) .
      ') VALUES (' .
      implode(
        ', ',
        array_map(function ($value) {
          return "'" . $value . "'";
        }, $columnValues)
      ) .
      ')';

    if ($conn->query($query_sql) === true) {
      http_response_code(200);
      echo json_encode([
        'status' => 'success',
        'data' => null,
        'message' => 'Insert Operation completed.',
        'error' => null,
      ]);
      return;
    } else {
      http_response_code(500);
      echo json_encode([
        'status' => 'error',
        'data' => null,
        'message' => 'SQL Error',
        'error' => [
          'code' => 500,
          'message' => $conn->error,
        ],
      ]);
      return;
    }
  } catch (Exception $error) {
    http_response_code(500);
    echo json_encode([
      'status' => 'error',
      'data' => null,
      'message' => 'Server Error',
      'error' => [
        'code' => 500,
        'message' => $error,
      ],
    ]);
    return;
  }

  return;
}
// Edit User
if ($method === 'PUT') {
  if (isset($_GET['id']) && is_numeric($_GET['id']) && $_GET['id'] > 0) {
    $userId = $_GET['id'];
    // check if user authorized
    $authorized = is_super_admin();
    if (!$authorized['authorized']) {
      http_response_code(401);
      echo json_encode([
        'status' => 'error',
        'data' => null,
        'message' => 'Unauthorized',
        'error' => [
          'code' => 401,
          'message' => $authorized['message'],
        ],
      ]);
      return;
    }
    $data = json_decode(file_get_contents('php://input'), true);
    // You can reset password and change email in other point, if you want
    // Sanitize Data
    $data = APISanitizer::sanitizeUser($data);
    extract($data);
    $errors = APIValidator::validateUser($data, true);
    if (!empty($errors)) {
      http_response_code(422);
      echo json_encode([
        'status' => 'error',
        'data' => null,
        'message' => 'Valdiation Error',
        'error' => [
          'code' => 422,
          'message' => $errors,
        ],
      ]);
      return;
    }
    // Hash the password
    if ($data['password']) {
      $password = password_hash($data['password'], PASSWORD_DEFAULT);
      $data['password'] = $password;
    }
    //Process the user data
    $columnNames = array_keys($data);
    $columnValues = array_values($data);
    $updatedClause = [];

    foreach ($columnNames as $columnName) {
      $updatedClause[] = "`$columnName` = '$data[$columnName]'";
    }
    $updatedColumns = implode(', ', $updatedClause);

    $query_sql = "Update `nx_users` SET $updatedColumns WHERE `id` = '$userId'";
    $result = $conn->query($query_sql);
    if ($result === true) {
      http_response_code(200);
      echo json_encode([
        'status' => 'success',
        'data' => null,
        'message' => 'Update Operation compeleted.',
        'error' => null,
      ]);
      return;
    } else {
      http_response_code(500);
      echo json_encode([
        'status' => 'error',
        'data' => null,
        'message' => 'SQL Error',
        'error' => [
          'code' => 500,
          'message' => $conn->error,
        ],
      ]);
    }
  } else {
    http_response_code(400);
    echo json_encode([
      'status' => 'error',
      'data' => null,
      'message' => 'Invalid ID',
      'error' => [
        'code' => 400,
        'message' => 'The ID must be a valid integer',
      ],
    ]);
  }

  exit();
}

// Delete User
if ($method === 'DELETE') {
  if (isset($_GET['id']) && is_numeric($_GET['id']) && $_GET['id'] > 0) {
    $userId = $_GET['id'];
    // check if user authorized
    $authorized = is_super_admin();
    if (!$authorized['authorized']) {
      http_response_code(401);
      echo json_encode([
        'status' => 'error',
        'data' => null,
        'message' => 'Unauthorized',
        'error' => [
          'code' => 401,
          'message' => $authorized['message'],
        ],
      ]);
      return;
    }
    // You cannot delete super admin
    $superUser = $authorized['data'];
    if ($userId == $superUser['id']) {
      http_response_code(401);
      echo json_encode([
        'status' => 'error',
        'data' => null,
        'message' => 'Unauthorized',
        'error' => [
          'code' => 401,
          'message' => 'You cannot delete your own record',
        ],
      ]);
      return;
    }

    try {
      $sql_query = "DELETE FROM nx_users WHERE `nx_users`.`id` = '$userId'";
      if ($conn->query($sql_query) === true) {
        http_response_code(200);
        echo json_encode([
          'status' => 'success',
          'data' => null,
          'message' => 'Delete Operation Completed',
          'error' => null,
        ]);
        return;
      } else {
        echo json_encode([
          'status' => 'error',
          'data' => null,
          'message' => 'SQL Error',
          'error' => [
            'code' => 500,
            'message' => $conn->error,
          ],
        ]);
        return;
      }
    } catch (Exception $error) {
      echo json_encode([
        'status' => 'error',
        'data' => null,
        'message' => 'Server Error',
        'error' => [
          'code' => 500,
          'message' => $error,
        ],
      ]);
      return;
    }
  } else {
    http_response_code(400);
    echo json_encode([
      'status' => 'error',
      'data' => null,
      'message' => 'Invalid ID',
      'error' => [
        'code' => 400,
        'message' => 'The ID must be a valid integer',
      ],
    ]);
    return;
  }

  exit();
}
