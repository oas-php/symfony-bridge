<?php declare(strict_types=1);

namespace OAS\Bridge\SymfonyBundle\Test;

use OAS\Bridge\SymfonyBundle\SchemaNotFound;
use OAS\Bridge\SymfonyBundle\Validation\ResponseValidator;
use OAS\Schema;
use OAS\Validator;
use OAS\Validator\SchemaConformanceFailure;
use Symfony\Component\HttpFoundation\Response;

/**
 * To be used by class which extends \Symfony\Bundle\FrameworkBundle\Test\KernelTestCase.
 *
 * @see \Symfony\Bundle\FrameworkBundle\Test\KernelTestCase
 *
 * @method fail(string $message): void
 * @method static getContainer()
 */
trait Assert
{
    private function assertValidAgainstSchema($value, Schema $schema): void
    {
        try {
            $this->getValidator()->validate($value, $schema);
            $this->markAsPassed();
        } catch (SchemaConformanceFailure $exception) {
            $this->fail(
                sprintf(
                    "Value\n\n%s\n\ndoes not conform to declared schema. %d violation(s) found: \n%s",
                    \json_encode($value, JSON_PRETTY_PRINT),
                    \count($exception->getViolations()),
                    $this->formatValidationErrors($exception)
                )
            );
        }
    }

    public function assertResponseBodyValidAgainstSchema(Response $response, string $method, string $uri, string $operationId = null): void
    {
        try {
            $this->getResponseValidator()->validate($response, $method, $uri, $operationId);
            $this->markAsPassed();
        } catch (Validator\SchemaConformanceFailure $exception) {
            $this->fail(
                sprintf(
                    "Response body of '%s %s' operation \n\n %s \n\n does not conform to declared schema. %d violation(s) found: \n%s",
                    $method,
                    $uri,
                    $response->getContent(),
                    count($exception->getViolations()),
                    $this->formatValidationErrors($exception)
                )
            );
        } catch (SchemaNotFound $exception) {
            $this->fail(
                sprintf(
                    "Schema is not declared for '%s %s' operation, status code %d and '%s' content-type.",
                    $method,
                    $uri,
                    $response->getStatusCode(),
                    $response->headers->get('content-type')
                )
            );
        }
    }

    private function formatValidationErrors(SchemaConformanceFailure $conformanceFailure): string
    {
        return array_reduce(
            iterator_to_array(
                $conformanceFailure->getViolations()
            ),
            fn (string $violations, Validator\ConstraintViolation $violation) => sprintf(
                "%s\n- value '%s' (at %s) is invalid: %s (constraint defined at %s)",
                $violations,
                json_encode($violation->getInvalidValue()),
                $violation->getInstancePath(),
                $violation->getMessage(),
                $violation->getSchemaPath()
            ),
            ''
        );
    }

    private function getValidator(): Validator
    {
        return static::getContainer()->get(Validator::class);
    }

    private function getResponseValidator(): ResponseValidator
    {
        return static::getContainer()->get(ResponseValidator::class);
    }

    private function markAsPassed(): void
    {
        $this->assertTrue(true);
    }
}