<?php

include_once '../db-config.php';
// Include the articles validator
include_once '../utils/ApiValidator.php';
// Include the articles sanitizer
include_once '../utils/ApiSanitizer.php';
// Include autoload.php
// composer require firebase/php-jwt
require_once '../vendor/autoload.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

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
    // Check if the user exists
    $sql_query = "SELECT * FROM `nx_users` WHERE `email` = '$email'";
    $result = $conn->query($sql_query);
    if ($result->num_rows === 1) {
      $user = $result->fetch_assoc();
      $db_password = $user['password'];
      if (password_verify($password, $db_password)) {
        // Generate Token
        // create payload
        $issued_at = time();
        $expire_time = time() + 60 * 60;
        $user_id = $user['id'];
        $user_email = $user['email'];
        $payload = [
          'iat' => $issued_at,
          'exp' => $expire_time,
          'user_id' => $user_id,
          'user_email' => $user_email,
        ];
        // Generate token
        $token = JWT::encode($payload, $secret_key, 'HS256');
        // Save token in the cookies
        $cookie_name = 'lwn_user_login_token';
        $cookie_value = $token;
        $cookie_expire = time() + 7 * 24 * 60 * 60;
        setCookie(
          $cookie_name,
          $cookie_value,
          $cookie_expire,
          '/',
          '',
          true,
          true
        );
        http_response_code(200);
        echo json_encode([
          'status' => 'success',
          'data' => $token,
          'message' => 'Login Operation Completed',
          'error' => null,
        ]);
        return;
      } else {
        // Password incorrect
        http_response_code(401);
        echo json_encode([
          'status' => 'error',
          'data' => null,
          'message' => 'Login Operation Failed',
          'error' => [
            'code' => 401,
            'message' => 'Incorrect Password',
          ],
        ]);
        return;
      }
      // Process the login
      // Todo: Save login somewhere: Tokens.....
    } else {
      // Login Failed
      http_response_code(401);
      echo json_encode([
        'status' => 'error',
        'message' => 'Login Operation Failed',
        'data' => null,
        'error' => [
          'code' => 401,
          'message' => 'The email is not registered with us.',
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
