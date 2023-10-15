<?php

include_once '../db-config.php';
include_once '../utils/is-authorized.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
  // get user
  $user = is_user_authorized();
  if ($user['authorized']) {
    http_response_code(200);
    echo json_encode([
      'status' => 'success',
      'message' => 'User is logged In',
      'data' => $user['data'],
      'error' => null,
    ]);
    return;
  } else {
    http_response_code(401);
    echo json_encode([
      'status' => 'error',
      'message' => 'Unauthorized',
      'data' => null,
      'error' => [
        'code' => 401,
        'message' => $user['message'],
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
      'message' => 'Only GET REQUESTS are accepted for whoami',
    ],
  ]);
  return;
}
