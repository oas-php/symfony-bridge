<?php declare(strict_types=1);

namespace OAS\Bridge\SymfonyBundle\Validation;

use OAS\Bridge\SymfonyBundle\Configuration;
use OAS\Validator;

class ValidatorFactory
{
    public static function create(Configuration $configuration): Validator
    {
        return new Validator(
            self::mapToValidatorConfiguration($configuration)
        );
    }

    private static function mapToValidatorConfiguration(Configuration $configuration): Validator\Configuration
    {
        $validatorConfiguration = new Validator\Configuration();
        $validatorConfiguration->yieldFormatSpecificError = $configuration->yieldFormatSpecificError();

        return $validatorConfiguration;
    }
}