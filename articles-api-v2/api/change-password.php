<?php
// Include Database Configuration
include_once '../db-config.php';
// Include the articles validator
include_once '../utils/ApiValidator.php';
// Include the articles sanitizer
include_once '../utils/ApiSanitizer.php';
// Include is-authorized
include_once '../utils/is-authorized.php';

// HTTP Method
$method = $_SERVER['REQUEST_METHOD'];

// POST an Article
if ($method === 'POST') {
  // Is the user logged in?
  $authorized = is_user_authorized();
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
  // The user is logged in
  try {
    // Get User Data
    $data = json_decode(file_get_contents('php://input'), true);
    $data = APISanitizer::sanitizeChangePassword($data);
    $errors = APIValidator::validateChangePassword($data);
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
    extract($data); // email/currentPassword/newPassword
    // check if data['email'] === $logged in user email
    $logged_in_user = $authorized['data'];
    $logged_in_user_email = $logged_in_user['email'];
    if ($logged_in_user_email !== $email) {
      http_response_code(401);
      echo json_encode([
        'status' => 'error',
        'data' => null,
        'message' => 'Unauthorized',
        'error' => [
          'code' => 401,
          'message' => "You cannot change other users' passwords",
        ],
      ]);
      return;
    }
    // Make sure that current password is correct
    $sql_query = "SELECT * FROM `nx_users` WHERE `email` = '$email'";
    $result = $conn->query($sql_query);
    if ($result->num_rows === 1) {
      $dbUser = $result->fetch_assoc();
      $dbPassword = $dbUser['password'];
      if (password_verify($currentPassword, $dbPassword)) {
        // correct password
        // change password here
        // hash the new password
        $hashNewPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        // send update request to the database
        $update_query = "UPDATE `nx_users` SET `password` = '$hashNewPassword' WHERE `email` = '$email'";
        if ($conn->query($update_query) === true) {
          // Password has been updated
          http_response_code(200);
          echo json_encode([
            'status' => 'success',
            'message' => 'Password has been changed successfully.',
            'data' => null,
            'error' => null,
          ]);
          return;
        } else {
          // SQL error
          http_response_code(500);
          echo json_encode([
            'status' => 'error',
            'message' => 'Database ERROR',
            'data' => null,
            'error' => [
              'code' => 500,
              'message' => 'Error on the database side: ' . $conn->error,
            ],
          ]);
          return;
        }
      } else {
        // incorrect password
        http_response_code(400);
        echo json_encode([
          'status' => 'error',
          'message' => 'Incorrect Fields',
          'data' => null,
          'error' => [
            'code' => 400,
            'message' => 'The current password field value is incorrect',
          ],
        ]);
        return;
      }
    } else {
      http_response_code(400);
      echo json_encode([
        'status' => 'error',
        'message' => 'Record Not Found',
        'data' => null,
        'error' => [
          'code' => 400,
          'message' => 'The logged in user record not found in our Database',
        ],
      ]);
      return;
    }
  } catch (Exception $error) {
    http_response_code(500);
    echo json_encode([
      'status' => 'error',
      'message' => 'Server Error',
      'data' => null,
      'error' => [
        'code' => 500,
        'message' => $error->getMessage(),
      ],
    ]);
    return;
  }
} else {
  http_response_code(405);
  echo json_encode([
    'status' => 'error',
    'message' => 'Invalid Request Method',
    'data' => null,
    'error' => [
      'code' => 405,
      'message' =>
        'Only POST Requests are accepted for changing the user password',
    ],
  ]);
}
