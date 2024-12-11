<?php

declare(strict_types=1);

use JsonSchema\Constraints\Constraint;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Wunderwerk\HttpApiUtils\HttpApiValidationTrait;

/**
 * Test class for HttpApiValidationTrait.
 */
#[CoversClass(HttpApiValidationTrait::class)]
final class HttpApiValidationTraitTest extends TestCase {

  use HttpApiValidationTrait;

  /**
   * The JSON schema to use for testing.
   *
   * @var array<string, mixed>
   */
  private array $schema = [
    "type" => "object",
    "properties" => [
      "name" => [
        "type" => "string",
      ],
      "description" => [
        "type" => "string",
      ],
      "age" => [
        "type" => "integer",
      ],
    ],
    "required" => ["name"],
  ];

  /**
   * Test validateDataStructure().
   */
  #[Test]
  public function canValidateDataStructure(): void {
    $validInput = (object) [
      'name' => 'Max',
      'description' => 'A person',
    ];

    $result = $this->validateDataStructure($validInput, $this->schema);
    $this->assertTrue($result->isValid());

    // Invalid input.
    $invalidInput = (object) [
      'description' => FALSE,
    ];

    $result = $this->validateDataStructure($invalidInput, $this->schema);
    $this->assertFalse($result->isValid());
    $this->assertEquals(2, count($result->getErrors()));
    $this->assertEquals('name', $result->getErrors()[0]['property']);
  }

  /**
   * Test validateDataStructure() with specific check mode.
   */
  #[Test]
  public function canValidateWithSpecificCheckMode(): void {
    // Age should be an integer.
    // With default check mode, the validation does not pass.
    $validInput = (object) [
      'name' => 'Max',
      'age' => '42',
    ];

    $result = $this->validateDataStructure($validInput, $this->schema);
    $this->assertFalse($result->isValid());

    // With coerce check mode, the validation will pass and age is coerced
    // to an integer.
    $validInput = (object) [
      'name' => 'Max',
      'age' => '42',
    ];

    $result = $this->validateDataStructure($validInput, $this->schema, Constraint::CHECK_MODE_COERCE_TYPES);
    $this->assertTrue($result->isValid());
    $this->assertEquals("integer", gettype($validInput->age));
  }

  /**
   * Test validateJsonString().
   */
  #[Test]
  public function canValidateJsonString(): void {
    $validInput = (string) json_encode([
      'name' => 'Max',
      'description' => 'A person',
    ]);

    $result = $this->validateJsonString($validInput, $this->schema);
    $this->assertTrue($result->isValid());

    // Invalid input.
    $invalidInput = (string) json_encode([
      'description' => FALSE,
    ]);

    $result = $this->validateJsonString($invalidInput, $this->schema);
    $this->assertFalse($result->isValid());
    $this->assertEquals(2, count($result->getErrors()));
    $this->assertEquals('name', $result->getErrors()[0]['property']);
  }

  /**
   * Test validateArray().
   */
  #[Test]
  public function canValidateArray(): void {
    $validInput = [
      'name' => 'Max',
      'description' => 'A person',
    ];

    $result = $this->validateArray($validInput, $this->schema);
    $this->assertTrue($result->isValid());

    // Invalid input.
    $invalidInput = [
      'description' => FALSE,
    ];

    $result = $this->validateArray($invalidInput, $this->schema);
    $this->assertFalse($result->isValid());
    $this->assertEquals(2, count($result->getErrors()));
    $this->assertEquals('name', $result->getErrors()[0]['property']);
  }

  /**
   * Test validateArray() with non-array input.
   */
  #[Test]
  public function canHandleNonArrayValidation(): void {
    $result = $this->validateArray("non-array", $this->schema);
    $this->assertFalse($result->isValid());
    $this->assertEquals('name', $result->getErrors()[0]['property']);

    $result = $this->validateArray(NULL, $this->schema);
    $this->assertFalse($result->isValid());
    $this->assertEquals('name', $result->getErrors()[0]['property']);

    $result = $this->validateArray(1, $this->schema);
    $this->assertFalse($result->isValid());
    $this->assertEquals('name', $result->getErrors()[0]['property']);

    $result = $this->validateArray(FALSE, $this->schema);
    $this->assertFalse($result->isValid());
    $this->assertEquals('name', $result->getErrors()[0]['property']);

    $result = $this->validateArray(new stdClass(), $this->schema);
    $this->assertFalse($result->isValid());
    $this->assertEquals('name', $result->getErrors()[0]['property']);
  }

  /**
   * Test validateDataStructure() with error response.
   */
  #[Test]
  public function canReturnErrorResponse(): void {
    $validInput = (object) [
      'name' => 'Max',
      'description' => 'A person',
    ];

    $result = $this->validateDataStructure($validInput, $this->schema);
    $this->assertNull($result->getResponse());

    // Invalid input.
    $invalidInput = (object) [
      'description' => FALSE,
    ];

    $result = $this->validateDataStructure($invalidInput, $this->schema);

    /** @var \Wunderwerk\JsonApiError\JsonApiErrorResponse $response */
    $response = $result->getResponse();

    $this->assertEquals(422, $response->getStatusCode());

    $expected = json_encode([
      'errors' => [
        [
          'status' => 422,
          'code' => 'validation_error',
          'source' => [
            'pointer' => '/name',
          ],
          'title' => 'Validation error',
          'detail' => 'Invalid property value for "name": The property name is required',
          'meta' => [
            'constraint' => 'required',
          ],
        ],
        [
          'status' => 422,
          'code' => 'validation_error',
          'source' => [
            'pointer' => '/description',
          ],
          'title' => 'Validation error',
          'detail' => 'Invalid property value for "description": Boolean value found, but a string is required',
          'meta' => [
            'constraint' => 'type',
          ],
        ],
      ],
    ], JSON_HEX_QUOT);

    $this->assertEquals($expected, $response->getContent());
  }

}
