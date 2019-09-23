<?php

declare(strict_types=1);

namespace Drupal\api_platform\DependencyInjection\Compiler;

use Drupal\api_platform\Core\Exception\RuntimeException;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class FilterPass implements CompilerPassInterface {

  /**
   * {@inheritdoc}
   *
   * @throws RuntimeException
   */
  public function process(ContainerBuilder $container)
  {
    $filters = [];

    $container->getDefinition('api_platform.filter_locator')->addArgument($filters);
  }

}
