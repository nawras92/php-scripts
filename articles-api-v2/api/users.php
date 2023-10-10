<?php

// Include Database Configuration
include_once '../db-config.php';

// HTTP Method
$method = $_SERVER['REQUEST_METHOD'];

// GET an article
if ($method === 'GET' && isset($_GET['id'])) {
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
