# HTTP-API Utils

This package provides useful utilities for use with HTTP-APIs of any kind. 

For response generation the `symfony/http-foundation` package is being used.

## Install

Install this package via composer:

```bash
composer require wunderwerkio/http-api-utils
```

## Features

### `HttpApiValidationTrait`

The `HttpApiValidationTrait` provides methods to validate a data structure against a [JSON Schema)(https://json-schema.org/).
The validation itself is done with the [`justinrainbow/json-schema`](https://github.com/justinrainbow/json-schema) package.

**Define the schema**:

```php
$schema = [
  "type" => "object",
  "properties" => [
    "name" => [
      "type" => "string",
    ],
    "age" => [
      "type" => "integer",
    ],
  ],
  "required" => ["name"],
];
```

Depending on your input data, you can use different validation methods to suit your needs:

- `validateDataStructure`
  Accepts a reference to an object. This is meant to be used with results from `json_decode`.
- `validateArray`
  Accepts an array as the data to validate. 
- `validateJsonString`
  Accepts a JSON encoded string.

**Example (with the schema from above)**:

```php
$validData = (object) [
  'name' => 'Max',
  'age' => 42,
];

$result = $this->validateDataStructure($validData, $schema);

$result->isValid(); // -> TRUE

// With invalid data.

$invalidData = (object) [
  'age' => '42',
];

$result = $this->validateDataStructure($invalidData, $schema);

$result->isValid(); // -> FALSE
$result->getErrors(); // -> Array of validation errors.
$result->getResponse(); // -> JsonApiErrorResponse with the validation errors.
```

For more information, check out the JSON Schema package: https://github.com/justinrainbow/json-schema.
