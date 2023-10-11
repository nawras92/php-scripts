<?php

$password = '111';
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

echo "The hash of '$password' is: '$hashed_password'";
echo '<br> .... </br>';
$password2 = '222';
$hashed_password2 = password_hash($password2, PASSWORD_DEFAULT);

echo "The hash of '$password2' is: '$hashed_password2'";
echo '<br> .... </br>';
// Generate secret key
$secret_key = bin2hex(random_bytes(32));
echo "The secret key is '$secret_key'";
