<?php declare(strict_types=1);

namespace OAS\Bridge\SymfonyBundle\EventListener;

use OAS\Bridge\SymfonyBundle\Annotation\Reader;
use OAS\Bridge\SymfonyBundle\Annotation\ValidateAgainstSchema;
use OAS\Bridge\SymfonyBundle\Configuration;
use OAS\Bridge\SymfonyBundle\SchemaNotFound;
use OAS\Bridge\SymfonyBundle\Validation\RequestValidator;
use OAS\Validator;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class OnControllerEventListener implements EventSubscriberInterface
{
    private RequestValidator $requestValidator;
    private Configuration $configuration;
    private Reader $annotationReader;

    public function __construct(
        RequestValidator $requestValidator,
        Configuration $configuration,
        Reader $annotationReader
    ) {
        $this->annotationReader = $annotationReader;
        $this->configuration = $configuration;
        $this->requestValidator = $requestValidator;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::CONTROLLER => [
                ['validateRequestBody']
            ]
        ];
    }

    /**
     * @throws Validator\SchemaConformanceFailure
     */
    public function validateRequestBody(ControllerEvent $controllerEvent): void
    {
        $request = $controllerEvent->getRequest();

        if (!$this->isMutatingRequest($request) && $this->configuration->validateMutatingRequestsOnly()) {
            return;
        }

        $annotation = $this->annotationReader->getCallableAnnotation(
            $controllerEvent->getController(),
            ValidateAgainstSchema::class
        );

        $hasAnnotation = $annotation instanceof ValidateAgainstSchema;

        if ($hasAnnotation || $this->configuration->validateAlways()) {
            $operationId = $hasAnnotation ? $annotation->operationId : null;

            try {
                $this->requestValidator->validate($request, $operationId);
            } catch (SchemaNotFound $exception) {
                if ($this->configuration->raiseErrorOnMissingSchema()) {
                    throw $exception;
                }
            }
        }
    }

    private function isMutatingRequest(Request $request): bool
    {
        $method = strtolower($request->getMethod());

        return in_array($method, ['post', 'put', 'patch', 'delete']);
    }
}

