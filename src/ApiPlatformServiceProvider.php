<?php

declare(strict_types=1);

namespace Drupal\api_platform;


use Drupal\api_platform\DependencyInjection\Compiler\CollectionDataProviderPass;
use Drupal\api_platform\DependencyInjection\Compiler\ConfigPass;
use Drupal\api_platform\DependencyInjection\Compiler\FilterPass;
use Drupal\api_platform\DependencyInjection\Compiler\ItemDataProviderPass;
use Drupal\api_platform\DependencyInjection\Compiler\SubresourceDataProviderPass;
use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DependencyInjection\ServiceProviderBase;
use Symfony\Component\PropertyInfo\DependencyInjection\PropertyInfoPass;

class ApiPlatformServiceProvider extends ServiceProviderBase {

  public function register(ContainerBuilder $container) {
    parent::register($container);

    // Add configuration passes.
    $container->addCompilerPass(new ConfigPass());
    $container->addCompilerPass(new FilterPass());

    // Add data provider passes.
    $container->addCompilerPass(new CollectionDataProviderPass());
    $container->addCompilerPass(new SubresourceDataProviderPass());
    $container->addCompilerPass(new ItemDataProviderPass());

    // Add property extractors.
    $container->addCompilerPass(new PropertyInfoPass());




//    if ($container->hasDefinition('config.factory')) {
//      $container->get('config.factory')->get('system.site')->get('name');
//      $container->setParameter('api_platform.title', '');
//    }

//    $container->setParameter('api_platform.title', '');
  }

}
