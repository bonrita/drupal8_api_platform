<?php

declare(strict_types=1);

namespace Drupal\api_platform;


use Drupal\api_platform\DependencyInjection\Compiler\ConfigPass;
use Drupal\api_platform\DependencyInjection\Compiler\FilterPass;
use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DependencyInjection\ServiceProviderBase;

class ApiPlatformServiceProvider extends ServiceProviderBase {

  public function register(ContainerBuilder $container) {
    parent::register($container);

    $container->addCompilerPass(new ConfigPass());
    $container->addCompilerPass(new FilterPass());



//    if ($container->hasDefinition('config.factory')) {
//      $container->get('config.factory')->get('system.site')->get('name');
//      $container->setParameter('api_platform.title', '');
//    }

//    $container->setParameter('api_platform.title', '');
  }

}
