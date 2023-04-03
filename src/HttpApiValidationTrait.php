<?php

declare(strict_types=1);

namespace Wunderwerk\HttpApiUtils;

use JsonSchema\Validator;
use Wunderwerk\HttpApiUtils\Validation\ValidationResult;

/**
 * Trait for HTTP API validation.
 */
trait HttpApiValidationTrait {

  /**
   * Validator instance.
   */
  protected Validator|NULL $validator = NULL;

  /**
   * Validate a data structure with given schema.
   *
   * @param object $data
   *   The data structure to validate.
   * @param array<string, mixed>|object $schema
   *   The JSON schema to validate against.
   * @param int|null $checkMode
   *   The check mode to use, see \JsonSchema\Validator::CHECK_MODE_*
   *
   * @return \Wunderwerk\HttpApiUtils\Validation\ValidationResult
   *   The validation result.
   */
  protected function validateDataStructure(object &$data, array|object $schema, ?int $checkMode = NULL) {
    $validator = $this->getValidator();

    $validator->validate($data, $schema, $checkMode);
    $errors = $validator->getErrors();

    return new ValidationResult($errors);
  }

  /**
   * Validate a JSON string with given schema.
   *
   * @param string $payload
   *   The JSON string to validate.
   * @param array<string, mixed>|object $schema
   *   The JSON schema to validate against.
   * @param int|null $checkMode
   *   The check mode to use, see \JsonSchema\Validator::CHECK_MODE_*
   *
   * @return \Wunderwerk\HttpApiUtils\Validation\ValidationResult
   *   The validation result.
   */
  protected function validateJsonString(string $payload, array|object $schema, ?int $checkMode = NULL) {
    $data = json_decode($payload);

    return $this->validateDataStructure($data, $schema, $checkMode);
  }

  /**
   * Validate a JSON object with given schema.
   *
   * @param array<mixed> $payload
   *   The JSON object to validate.
   * @param array<string, mixed>|object $schema
   *   The JSON schema to validate against.
   * @param int|null $checkMode
   *   The check mode to use, see \JsonSchema\Validator::CHECK_MODE_*
   *
   * @return \Wunderwerk\HttpApiUtils\Validation\ValidationResult
   *   The validation result.
   */
  protected function validateArray(array $payload, array|object $schema, ?int $checkMode = NULL) {
    $data = $this->getValidator()->arrayToObjectRecursive($payload);

    return $this->validateDataStructure($data, $schema, $checkMode);
  }

  /**
   * Get the JSON Schema validator.
   *
   * @return \JsonSchema\Validator
   *   The JSON Schema validator.
   */
  protected function getValidator(): Validator {
    if (is_null($this->validator)) {
      $this->validator = new Validator();
    }

    $this->validator->reset();

    return $this->validator;
  }

}
