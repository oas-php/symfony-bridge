<?php declare(strict_types=1);

namespace OAS\Bridge\SymfonyBundle\DependencyInjection;

use OAS\Bridge\SymfonyBundle\EventListener\OnControllerEventListener;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Resource\DirectoryResource;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Extension\Extension;
use OAS\Bridge\SymfonyBundle\Configuration as OASConfiguration;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\DependencyInjection\Parameter;

class OASExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $processedConfiguration = $this->processConfiguration(new Configuration(), $configs);

        foreach ($processedConfiguration['specs'] as $spec) {
            if (array_key_exists('resources_dir', $spec)) {
                $container->addResource(new DirectoryResource($spec['resources_dir']));
            }
        }

        $this->registerConfiguration($container, $processedConfiguration);
        $this->registerListener($container);
        $this->loadServices($container);
    }

    private function loadServices(ContainerBuilder $containerBuilder): void
    {
        $environment = $containerBuilder->getParameter('kernel.environment');
        $loader = new YamlFileLoader($containerBuilder, new FileLocator(__DIR__.'/../Resources/config'));

        $loader->load('services.yaml');

        if ('test' === $environment) {
            $loader->load('services_test.yaml');
        }
    }

    private function registerConfiguration(ContainerBuilder $containerBuilder, array $processedConfiguration): void
    {
        $containerBuilder->setDefinition(
            OASConfiguration::class,
            new Definition(
                OASConfiguration::class,
                [
                    array_merge(
                        $processedConfiguration,
                        ['cache_dir' => new Parameter('kernel.cache_dir')]
                    )
                ]
            )
        );
    }

    private function registerListener(ContainerBuilder $containerBuilder): void
    {
        $listenerDefinition = new Definition(OnControllerEventListener::class);
        $listenerDefinition->setAutowired(true);
        $listenerDefinition->addTag('kernel.event_subscriber');
        $containerBuilder->setDefinition(OnControllerEventListener::class, $listenerDefinition);
    }
}
