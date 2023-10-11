<?php

include_once '../db-config.php';
// Include autoload.php
// composer require firebase/php-jwt
require_once '../vendor/autoload.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // check if the user logged in
  if (isset($_COOKIE['lwn_user_login_token'])) {
    // logged in
    // Delete cookies
    setCookie('lwn_user_login_token', '', time() - 3600, '/');
    // logged out
    http_response_code(200);
    echo json_encode([
      'status' => 'success',
      'message' => 'Logout Operation Completed',
      'data' => null,
      'error' => null,
    ]);
    return;
  } else {
    // logged out already
    http_response_code(200);
    echo json_encode([
      'status' => 'success',
      'message' => 'already Logout Out',
      'data' => null,
      'error' => null,
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
      'message' => 'Only POST REQUESTS are accepted for Logout',
    ],
  ]);
  return;
}
