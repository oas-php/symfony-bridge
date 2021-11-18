<?php declare(strict_types=1);

namespace OAS\Bridge\SymfonyBundle\DependencyInjection;

use OAS\Bridge\SymfonyBundle\EventListener\OnControllerEventListener;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Extension\Extension;
use OAS\Bridge\SymfonyBundle\Configuration as OASConfiguration;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class OASExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $processedConfiguration = $this->processConfiguration(new Configuration(), $configs);

        $this->registerConfiguration($container, $processedConfiguration);
        $this->registerListener($container);
        $this->loadServices($container);
    }

    private function loadServices(ContainerBuilder $containerBuilder): void
    {
        $loader = new YamlFileLoader(
            $containerBuilder,
            new FileLocator(__DIR__.'/../Resources/config')
        );

        $loader->load('services.yaml');
    }

    private function registerConfiguration(ContainerBuilder $containerBuilder, array $processedConfiguration): void
    {
        $containerBuilder->setDefinition(
            OASConfiguration::class,
            new Definition(
                OASConfiguration::class,
                [$processedConfiguration]
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