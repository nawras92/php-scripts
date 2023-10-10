<?php

include_once '../db-config.php';
// Include the articles validator
include_once '../utils/ApiValidator.php';
// Include the articles sanitizer
include_once '../utils/ApiSanitizer.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $data = json_decode(file_get_contents('php://input'), true);
  if (isset($data['email']) && isset($data['password'])) {
    //  Sanitize the input
    $data = APISanitizer::sanitizeLogin($data);
    //  Validate the input
    $errors = APIValidator::validateLogin($data);
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
    // Extract email and password
    extract($data); // $email/$password
    // Todo: hash the provided password
    // Todo: search for hashed password
    // Check if the user exists
    $sql_query = "SELECT * FROM `nx_users` WHERE `email` = '$email' AND `password` = '$password'";
    $result = $conn->query($sql_query);
    if ($result->num_rows === 1) {
      $user = $result->fetch_assoc();
      // Process the login
      // Todo: Save login somewhere: Tokens.....
      http_response_code(200);
      echo json_encode([
        'status' => 'success',
        'data' => $user,
        'message' => 'Login Operation Completed',
        'error' => null,
      ]);
      return;
    } else {
      // Login Failed
      http_response_code(401);
      echo json_encode([
        'status' => 'error',
        'message' => 'Login Operation Failed',
        'data' => null,
        'error' => [
          'code' => 401,
          'message' => 'Invalid email or password',
        ],
      ]);
      return;
    }

    return;
  } else {
    http_response_code(400);
    echo json_encode([
      'status' => 'error',
      'message' => 'Missing Fields',
      'data' => null,
      'error' => [
        'code' => 400,
        'message' => 'Email and Password Fields are Required.',
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
      'message' => 'Only POST REQUESTS are accepted for login',
    ],
  ]);
  return;
}
