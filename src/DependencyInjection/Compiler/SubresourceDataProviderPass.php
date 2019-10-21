<?php

declare(strict_types=1);

namespace Drupal\api_platform\DependencyInjection\Compiler;


use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class SubresourceDataProviderPass implements CompilerPassInterface {

  /**
   * @inheritDoc
   */
  public function process(ContainerBuilder $container) {
    if (!$container->hasDefinition('api_platform.subresource_data_provider')) {
      return;
    }

    $definition = $container->getDefinition('api_platform.subresource_data_provider');
    $services = $container->findTaggedServiceIds('api_platform.subresource_data_provider');
    foreach ($services as $id => $attributes) {
      $definition->addMethodCall('setDataProvider', [$id]);
    }

  }

}
