<?php

class APIValidator
{
  public static function validateArticle($article, $edit = false)
  {
    $schema = [
      'title' => [
        'required' => !$edit,
        'min_length' => 5,
        'max_length' => 80,
      ],
      'description' => [
        'required' => !$edit,
        'min_length' => 0,
        'max_length' => 160,
      ],
      'content' => [
        'required' => false,
        'min_length' => 0,
        'max_length' => 5000,
      ],
      'author_id' => [
        'required' => !$edit,
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

  public static function validateLogin($loginData)
  {
    $schema = [
      'email' => [
        'required' => true,
        'type' => 'email',
      ],
      'password' => [
        'required' => true,
        'min_length' => 3,
        'max_length' => 30,
      ],
    ];
    // Get Errors
    $errors = [];
    foreach ($schema as $field => $rules) {
      // if required, add error
      if ($rules['required'] && !isset($loginData[$field])) {
        $errors[$field] = "Field '$field' is required";
      }
      // Check min length
      if (
        isset($loginData[$field]) &&
        isset($rules['min_length']) &&
        strlen($loginData[$field]) < $rules['min_length']
      ) {
        $errors[
          $field
        ] = "Field '$field' must be at least {$rules['min_length']} characters long";
      }
      // Check the max length
      if (
        isset($loginData[$field]) &&
        isset($rules['max_length']) &&
        strlen($loginData[$field]) > $rules['max_length']
      ) {
        $errors[
          $field
        ] = "Field '$field' must not exceed {$rules['max_length']} characters long";
      }
      // Check data type
      if (isset($loginData[$field]) && isset($rules['type'])) {
        if ($rules['type'] === 'email') {
          if (!filter_var($loginData[$field], FILTER_VALIDATE_EMAIL)) {
            $errors[$field] = "Field '$field' must be of type email ";
          }
        }
      }
    }

    return $errors;
  }
  public static function validateUser($user, $edit = false)
  {
    $schema = [
      'email' => [
        'required' => !$edit,
        'type' => 'email',
      ],
      'password' => [
        'required' => !$edit,
        'min_length' => 3,
        'max_length' => 50,
      ],
      'firstName' => [
        'required' => false,
        'min_length' => 3,
        'max_length' => 50,
      ],
      'lastName' => [
        'required' => false,
        'min_length' => 3,
        'max_length' => 50,
      ],
      'role' => [
        'required' => false,
        'type' => 'enum',
        'values' => ['AUTHOR', 'SUPERADMIN'],
      ],
    ];
    // Get Errors
    $errors = [];
    foreach ($schema as $field => $rules) {
      // if required, add error
      if ($rules['required'] && !isset($user[$field])) {
        $errors[$field] = "Field '$field' is required";
      }
      // Check min length
      if (
        isset($user[$field]) &&
        isset($rules['min_length']) &&
        strlen($user[$field]) < $rules['min_length']
      ) {
        $errors[
          $field
        ] = "Field '$field' must be at least {$rules['min_length']} characters long";
      }
      // Check the max length
      if (
        isset($user[$field]) &&
        isset($rules['max_length']) &&
        strlen($user[$field]) > $rules['max_length']
      ) {
        $errors[
          $field
        ] = "Field '$field' must not exceed {$rules['max_length']} characters long";
      }
      // validate type field
      if (isset($user[$field]) && isset($rules['type'])) {
        // validate email
        if ($rules['type'] === 'email') {
          if (!filter_var($user[$field], FILTER_VALIDATE_EMAIL)) {
            $errors[$field] = "Field '$field' must be of type email ";
          }
        }
        // validate enum
        if ($rules['type'] === 'enum') {
          if (!in_array($user[$field], $rules['values'])) {
            $valuesString = implode(', ', $rules['values']);
            $errors[
              $field
            ] = "Field '$field' value must be in `{$valuesString}`";
          }
        }
      }
    }

    return $errors;
  }
}
