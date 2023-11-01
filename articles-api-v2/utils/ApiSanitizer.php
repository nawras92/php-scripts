<?php

class APISanitizer
{
  public static function sanitizeArticle($article)
  {
    $cleanData = [];
    foreach ($article as $key => $value) {
      $cleanValue = trim(strip_tags($value));
      $cleanData[$key] = $cleanValue;
    }

    return $cleanData;
  }
  public static function sanitizeLogin($loginData)
  {
    $cleanData = [];
    foreach ($loginData as $key => $value) {
      $cleanValue = trim(strip_tags($value));
      $cleanData[$key] = $cleanValue;
    }

    return $cleanData;
  }
  public static function sanitizeUser($userData)
  {
    $cleanData = [];
    foreach ($userData as $key => $value) {
      $cleanValue = trim(strip_tags($value));
      $cleanData[$key] = $cleanValue;
    }

    return $cleanData;
  }
  public static function sanitizeChangePassword($userData)
  {
    $cleanData = [];
    foreach ($userData as $key => $value) {
      $cleanValue = trim(strip_tags($value));
      $cleanData[$key] = $cleanValue;
    }

    return $cleanData;
  }
}
