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

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
  //check if cookie exists
  if (isset($_COOKIE['lwn_user_login_token'])) {
    //ok it exists
    $token = $_COOKIE['lwn_user_login_token'];
    $decoded = JWT::decode($token, new Key($secret_key, 'HS256'));
    // Todo: Verify the token/ expire
    http_response_code(200);
    echo json_encode([
      'status' => 'success',
      'message' => 'User is Logged In',
      'data' => $decoded,
      'error' => null,
    ]);
    return;
  } else {
    // not logged
    http_response_code(401);
    echo json_encode([
      'status' => 'error',
      'message' => 'Not Logged In',
      'data' => null,
      'error' => [
        'code' => 401,
        'message' => 'User is not logged in',
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
      'message' => 'Only GET REQUESTS are accepted for this point',
    ],
  ]);
  return;
}
