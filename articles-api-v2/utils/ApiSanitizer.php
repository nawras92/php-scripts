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
}
