<?php

class APIValidator
{
  public static function validateArticle($article)
  {
    $schema = [
      'title' => [
        'required' => true,
        'min_length' => 5,
        'max_length' => 80,
      ],
      'description' => [
        'required' => true,
        'min_length' => 0,
        'max_length' => 160,
      ],
      'content' => [
        'required' => false,
        'min_length' => 0,
        'max_length' => 5000,
      ],
      'author_id' => [
        'required' => true,
        'type' => 'integer',
      ],
      'category' => [
        'required' => false,
        'min_length' => 0,
        'max_length' => 255,
      ],
      'keywords' => [
        'required' => false,
        'min_length' => 0,
        'max_length' => 255,
      ],
    ];
    // Get Errors
    $errors = [];
    foreach ($schema as $field => $rules) {
      // if required, add error
      if ($rules['required'] && !isset($article[$field])) {
        $errors[$field] = "Field '$field' is required";
      }
      // Check min length
      if (
        isset($article[$field]) &&
        isset($rules['min_length']) &&
        strlen($article[$field]) < $rules['min_length']
      ) {
        $errors[
          $field
        ] = "Field '$field' must be at least {$rules['min_length']} characters long";
      }
      // Check the max length
      if (
        isset($article[$field]) &&
        isset($rules['max_length']) &&
        strlen($article[$field]) > $rules['max_length']
      ) {
        $errors[
          $field
        ] = "Field '$field' must not exceed {$rules['max_length']} characters long";
      }
      // Check data type
      if (isset($article[$field]) && isset($rules['type'])) {
        $intValOfField = intval($article[$field]);
        if ($intValOfField === 0) {
          $errors[$field] = "Field '$field' must be of type {$rules['type']}";
        }
        $type = gettype(intval($article[$field]));
        if ($type !== $rules['type']) {
          $errors[$field] = "Field '$field' must be of type {$rules['type']}";
        }
      }
    }

    return $errors;
  }
}
