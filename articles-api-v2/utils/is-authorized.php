<?php

// include db config
include_once '../db-config.php';
// Include autoload.php
// composer require firebase/php-jwt
require_once '../vendor/autoload.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

function verifyToken($token)
{
  try {
    global $secret_key;
    global $conn;
    // Decode token: payload: email id, exp
    $decoded = JWT::decode($token, new Key($secret_key, 'HS256'));
    $user_email = $decoded->user_email;
    $user_id = $decoded->user_id;
    $exp = $decoded->exp;
    // is expired?
    $current_time = time();
    if ($exp < $current_time) {
      return false;
    }
    // id/email exists in our db
    $sql_query = "SELECT * FROM `nx_users` WHERE `id` = '$user_id' AND `email` = '$user_email'";
    $result = $conn->query($sql_query);
    if ($result->num_rows > 0) {
      return true;
    }

    return false;
  } catch (Exception $e) {
    return false;
  }
}
function is_user_authorized()
{
  if (
    !array_key_exists('HTTP_AUTHORIZATION', $_SERVER) ||
    !startsWith($_SERVER['HTTP_AUTHORIZATION'], 'Bearer')
  ) {
    return [
      'authorized' => false,
      'message' => 'There is no Authorization Header',
    ];
  }

  $token = substr($_SERVER['HTTP_AUTHORIZATION'], 7);
  if (verifyToken($token)) {
    // valid
    return [
      'authorized' => true,
      'message' => 'valid token',
    ];
  } else {
    return [
      'authorized' => false,
      'message' => 'Authorization token not valid',
    ];
  }
}

// Starts with
function startsWith($string, $substring)
{
  return strpos($string, $substring) === 0;
}
