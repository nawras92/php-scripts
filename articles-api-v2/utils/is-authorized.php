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
      return ['valid' => false, 'message' => 'Token Expired'];
    }
    // id/email exists in our db
    $sql_query = "SELECT * FROM `nx_users` WHERE `id` = '$user_id' AND `email` = '$user_email'";
    $result = $conn->query($sql_query);
    if ($result->num_rows > 0) {
      $user = $result->fetch_assoc();
      return ['valid' => true, 'data' => $user];
    }

    return ['valid' => false, 'message' => 'Token not Valid'];
  } catch (Exception $e) {
    return ['valid' => false, 'message' => 'Token not Valid'];
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
  $is_valid = verifyToken($token); // [valid: true/false, data: user]
  if ($is_valid['valid']) {
    // valid
    return [
      'authorized' => true,
      'data' => $is_valid['data'],
      'message' => 'valid token',
    ];
  } else {
    return [
      'authorized' => false,
      'message' => 'Authorization token not valid' . $is_valid['message'],
    ];
  }
}

function is_owner($articleId)
{
  global $conn;
  // check if user is logged In
  $is_logged_in = is_user_authorized();
  if (!$is_logged_in['authorized']) {
    return [
      'authorized' => false,
      'message' => $is_logged_in['message'],
    ];
  }
  $user = $is_logged_in['data'];
  $user_id = $user['id'];
  $user_role = $user['role'];
  // if logged in user is super admin, you can edit/delete
  if ($user_role === 'SUPERADMIN') {
    return [
      'authorized' => true,
      'message' => 'Super user has the right to manage all the articles',
      'data' => $user,
    ];
  }
  // Check if the logged in user, is the author
  $sql_query = "SELECT * FROM `nx_articles` WHERE `id` = '$articleId' AND `author_id` = '$user_id'";
  $result = $conn->query($sql_query);
  if ($result->num_rows > 0) {
    $article = $result->fetch_assoc();
    return [
      'authorized' => true,
      'message' => 'The logged in user has the right to manage this article',
      'data' => $user,
    ];
  }

  return [
    'authorized' => false,
    'message' => 'The logged in user has no right to manage this article',
  ];
}

function is_super_admin()
{
  // check if user is logged In
  $is_logged_in = is_user_authorized();
  if (!$is_logged_in['authorized']) {
    return [
      'authorized' => false,
      'message' => $is_logged_in['message'],
    ];
  }
  $user = $is_logged_in['data'];
  $user_role = $user['role'];
  if ($user_role === 'SUPERADMIN') {
    return [
      'authorized' => true,
      'message' => 'The logged in user is super admin',
      'data' => $user,
    ];
  }
  return [
    'authorized' => false,
    'message' => 'This user is not a super admin',
  ];
}

// Starts with
function startsWith($string, $substring)
{
  return strpos($string, $substring) === 0;
}
