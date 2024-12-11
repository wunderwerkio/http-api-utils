<?php

declare(strict_types=1);

namespace Wunderwerk\HttpApiUtils\Validation;

use Wunderwerk\JsonApiError\JsonApiError;
use Wunderwerk\JsonApiError\JsonApiErrorResponse;

/**
 * Class to represent validation results.
 */
class ValidationResult {

  /**
   * If the validation was successful.
   */
  protected readonly bool $isValid;

  /**
   * Construct new validation result.
   *
   * @param array{property: string, pointer: string, message: string, constraint: string, pattern?: string}[] $errors
   *   Array of validation errors.
   */
  public function __construct(
    protected readonly array $errors,
  ) {
    $this->isValid = empty($errors);
  }

  /**
   * If the validation was successful.
   */
  public function isValid(): bool {
    return $this->isValid;
  }

  /**
   * Get the validation errors.
   *
   * @return array{property: string, pointer: string, message: string, constraint: string, pattern?: string}[]
   *   The validation errors.
   */
  public function getErrors(): array {
    return $this->errors;
  }

  /**
   * Get a JSON API error response.
   *
   * @param array<string, mixed> $headers
   *   Additional headers to add to the response.
   *
   * @return \Wunderwerk\JsonApiError\JsonApiErrorResponse|null
   *   The JSON API error response, NULL if validation was successful.
   */
  public function getResponse(
    array $headers = [],
  ): ?JsonApiErrorResponse {
    if ($this->isValid()) {
      return NULL;
    }

    $errors = [];

    foreach ($this->getErrors() as $error) {
      $errors[] = new JsonApiError(
        status: 422,
        code: 'validation_error',
        title: 'Validation error',
        detail: sprintf('Invalid property value for "%s": %s', $error['property'], $error['message']),
        source: [
          'pointer' => $error['pointer'],
        ],
        meta: [
          'constraint' => $error['constraint'],
        ],
      );
    }

    return new JsonApiErrorResponse($errors, $headers);
  }

}
