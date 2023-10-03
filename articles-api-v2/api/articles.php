<?php

// Include Database Configuration
include_once '../db-config.php';
// Include the articles validator
include_once '../utils/ApiValidator.php';
// Include the articles sanitizer
include_once '../utils/ApiSanitizer.php';

// HTTP Method
$method = $_SERVER['REQUEST_METHOD'];

// GET a single article
if ($method === 'GET' && isset($_GET['id'])) {
  // Validate ID
  if (is_numeric($_GET['id']) && $_GET['id'] > 0) {
    $articleId = $_GET['id'];
    try {
      // Send Query to DB
      $sql_query = "SELECT * FROM `nx_articles` WHERE id = '$articleId'";
      $result = $conn->query($sql_query);
      if ($result->num_rows === 1) {
        $article = $result->fetch_assoc();
        http_response_code(200);
        echo json_encode([
          'status' => 'success',
          'data' => $article,
          'message' => 'Operation completed!',
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
            'message' => 'The requested article not found',
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
}

// Get All articles
if ($method === 'GET') {
  $articles = [];
  try {
    $sql_query = 'SELECT * FROM `nx_articles`';
    $result = $conn->query($sql_query);
    if ($result->num_rows > 0) {
      while ($row = $result->fetch_assoc()) {
        $articles[] = $row;
      }
      http_response_code(200);
      echo json_encode([
        'status' => 'success',
        'data' => $articles,
        'message' => 'Operation Completed',
        'error' => null,
      ]);
      return;
    } else {
      http_response_code(404);
      echo json_encode([
        'status' => 'error',
        'data' => null,
        'message' => 'Records Not Found',
        'error' => [
          'code' => 404,
          'message' => 'There are no articles Found',
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
}

// POST an Article
if ($method === 'POST') {
  try {
    // Get Data
    $data = json_decode(file_get_contents('php://input'), true);
    // Sanitize Data
    $data = APISanitizer::sanitizeArticle($data);
    extract($data);
    $errors = APIValidator::validateArticle($data);
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
    // Does the $author_id exist?
    $query_user = "SELECT * FROM `nx_users` WHERE `id` = '$author_id'";
    $result = $conn->query($query_user);
    if ($result->num_rows === 0) {
      http_response_code(404);
      echo json_encode([
        'status' => 'error',
        'data' => null,
        'message' => 'Record not found.',
        'error' => [
          'code' => 404,
          'message' => 'The provided author_id has no record in the database',
        ],
      ]);
      return;
    }

    // Process the user data
    $columnNames = array_keys($data);
    $columnValues = array_values($data);
    $query_sql =
      'INSERT INTO `nx_articles` (' .
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
  exit();
}

// Delete an article
if ($method === 'DELETE') {
  if (isset($_GET['id']) && is_numeric($_GET['id']) && $_GET['id'] > 0) {
    $articleId = $_GET['id'];
    try {
      $sql_query = "DELETE FROM nx_articles WHERE `nx_articles`.`id` = '$articleId'";
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

// Update an article
if ($method === 'PUT') {
  if (isset($_GET['id']) && is_numeric($_GET['id']) && $_GET['id'] > 0) {
    $articleId = $_GET['id'];
    $data = json_decode(file_get_contents('php://input'), true);
    // Sanitize Data
    $data = APISanitizer::sanitizeArticle($data);
    extract($data);
    $errors = APIValidator::validateArticle($data);
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
    // Check if the article exists
    $query_article = "SELECT * FROM `nx_articles` WHERE `id` = '$articleId'";
    $result = $conn->query($query_article);
    if ($result->num_rows === 0) {
      http_response_code(404);
      echo json_encode([
        'status' => 'error',
        'data' => null,
        'message' => 'Record not found.',
        'error' => [
          'code' => 404,
          'message' => 'The provided article_id has no record in the database',
        ],
      ]);
      return;
    }
    // Does the $author_id exist?
    $query_user = "SELECT * FROM `nx_users` WHERE `id` = '$author_id'";
    $result = $conn->query($query_user);
    if ($result->num_rows === 0) {
      http_response_code(404);
      echo json_encode([
        'status' => 'error',
        'data' => null,
        'message' => 'Record not found.',
        'error' => [
          'code' => 404,
          'message' => 'The provided author_id has no record in the database',
        ],
      ]);
      return;
    }
    //Process the user data
    $columnNames = array_keys($data);
    $columnValues = array_values($data);
    $updatedClause = [];

    foreach ($columnNames as $columnName) {
      $updatedClause[] = "`$columnName` = '$data[$columnName]'";
    }
    $updatedColumns = implode(', ', $updatedClause);

    $query_sql = "Update `nx_articles` SET $updatedColumns WHERE `id` = '$articleId'";
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
