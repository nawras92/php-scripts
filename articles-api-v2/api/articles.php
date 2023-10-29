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

// GET Articles by  Author Id
if ($method === 'GET' && isset($_GET['author_id'])) {
  // Validate ID
  if (is_numeric($_GET['author_id']) && $_GET['author_id'] > 0) {
    $author_id = $_GET['author_id'];
    try {
      // Send Query to DB
      $sql_query = "SELECT * FROM `nx_articles` WHERE author_id = '$author_id'";
      $result = $conn->query($sql_query);
      if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
          $articles[] = $row;
        }
        http_response_code(200);
        echo json_encode([
          'status' => 'success',
          'data' => $articles,
          'message' => 'Operation completed!',
          'error' => null,
        ]);
        return;
      } else {
        http_response_code(200);
        echo json_encode([
          'status' => 'success',
          'data' => [],
          'message' => 'There are no articles written by this user',
          'error' => 'null',
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
        'message' => 'The Author ID must be a valid integer',
      ],
    ]);
    return;
  }
}
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
  // get current page from url
  $page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
  // get items per page from url
  $perPage = isset($_GET['perPage']) ? (int) $_GET['perPage'] : 5;
  // get order
  $order = isset($_GET['order']) && $_GET['order'] === 'asc' ? 'asc' : 'desc';
  $searchQuery = isset($_GET['search']) ? $_GET['search'] : '';

  try {
    // Get articles count
    $countQuery = 'SELECT COUNT(*) as total FROM `nx_articles`';
    $countResult = $conn->query($countQuery);
    $total_records = $countResult->fetch_assoc()['total'];

    // number of pages
    $total_pages = ceil($total_records / $perPage);

    // calculate offset
    $offset = ($page - 1) * $perPage;

    $sql_query = "SELECT * FROM `nx_articles` WHERE `title` LIKE '%$searchQuery%' OR `description` like '%$searchQuery%' OR `content` like '%$searchQuery%' ORDER BY `id` $order LIMIT $perPage OFFSET $offset";
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
        'meta' => [
          'page' => $page,
          'perPage' => $perPage,
          'orderBy' => 'id',
          'order' => $order,
          'search' => $searchQuery,
          'count' => (int) $total_records,
          'totalPages' => (int) $total_pages,
        ],
      ]);
      return;
    } else {
      http_response_code(200);
      echo json_encode([
        'status' => 'success',
        'data' => [],
        'message' => 'There are no records',
        'error' => null,
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
    // check if user authorized
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
    // User is authorized
    $user = $authorized['data'];
    // Get Data
    $data = json_decode(file_get_contents('php://input'), true);
    $data['author_id'] = $user['id'];
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
    // check if user authorized
    $authorized = is_owner($articleId);
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
    // check if user authorized
    $authorized = is_owner($articleId);
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
    // Sanitize Data
    $data = APISanitizer::sanitizeArticle($data);
    extract($data);
    $errors = APIValidator::validateArticle($data, true);
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
