<?php

declare(strict_types=1);

namespace Drupal\api_platform\DependencyInjection\Compiler;

use Doctrine\Common\Annotations\Annotation;
use Drupal\api_platform\DependencyInjection\Configuration;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Resource\DirectoryResource;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Parameter;

class ConfigPass implements CompilerPassInterface {

  private $processedConfigs = [];

  /**
   * {@inheritdoc}
   *
   *
   */
  public function process(ContainerBuilder $container) {
    $configs = [];

    $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));

    $configuration = new Configuration();
    $config = $this->processConfiguration($configuration, $configs);

    $formats = $this->getFormats($config['formats']);
    $errorFormats = $this->getFormats($config['error_formats']);

    $this->registerCommonConfiguration($container, $config, $loader, $formats, $errorFormats);
    $this->registerMetadataConfiguration($container, $config, $loader);
    $this->registerSwaggerConfiguration($container, $config, $loader);
    $this->registerJsonProblemConfiguration($errorFormats, $loader);
    $this->registerOAuthConfiguration($container, $config);
    $this->registerJsonLdHydraConfiguration($container, $formats, $loader, $config['enable_docs']);
    $this->registerGraphQlConfiguration($container, $config, $loader);
//    $this->registerCacheConfiguration($container, $config);
    $this->registerDoctrineOrmConfiguration($container, $config, $loader);
  }

  private function registerCommonConfiguration(ContainerBuilder $container, $config, XmlFileLoader $loader, $formats,  array $errorFormats) {
    $loader->load('symfony.xml');
    $loader->load('drupal.xml');

    $loader->load('api.xml');
    $loader->load('data_provider.xml');
    $loader->load('filter.xml');


    $container->setParameter('api_platform.enable_entrypoint', $config['enable_entrypoint']);
    $container->setParameter('api_platform.enable_docs', $config['enable_docs']);
    $container->setParameter('api_platform.title', $config['title']);
    $container->setParameter('api_platform.description', $config['description']);
    $container->setParameter('api_platform.version', $config['version']);
    $container->setParameter('api_platform.show_webby', $config['show_webby']);
    $container->setParameter('api_platform.exception_to_status', $config['exception_to_status']);
    $container->setParameter('api_platform.formats', $formats);
    $container->setParameter('api_platform.error_formats', $errorFormats);
    $container->setParameter('api_platform.collection.pagination.enabled', $this->isConfigEnabled($container, $config['collection']['pagination']));
    $container->setParameter('api_platform.collection.pagination.partial', $config['collection']['pagination']['partial']);
    $container->setParameter('api_platform.collection.pagination.client_enabled', $config['collection']['pagination']['client_enabled']);
    $container->setParameter('api_platform.collection.pagination.client_items_per_page', $config['collection']['pagination']['client_items_per_page']);
    $container->setParameter('api_platform.collection.pagination.client_partial', $config['collection']['pagination']['client_partial']);
    $container->setParameter('api_platform.collection.pagination.items_per_page', $config['collection']['pagination']['items_per_page']);
    $container->setParameter('api_platform.collection.pagination.maximum_items_per_page', $config['collection']['pagination']['maximum_items_per_page']);
    $container->setParameter('api_platform.collection.pagination.page_parameter_name', $config['collection']['pagination']['page_parameter_name']);
    $container->setParameter('api_platform.collection.pagination.enabled_parameter_name', $config['collection']['pagination']['enabled_parameter_name']);
    $container->setParameter('api_platform.collection.pagination.items_per_page_parameter_name', $config['collection']['pagination']['items_per_page_parameter_name']);
    $container->setParameter('api_platform.collection.pagination.partial_parameter_name', $config['collection']['pagination']['partial_parameter_name']);
    $container->setParameter('api_platform.collection.pagination', $config['collection']['pagination']);

    $container->setAlias('api_platform.path_segment_name_generator', $config['path_segment_name_generator']);

  }

  private function registerMetadataConfiguration(ContainerBuilder $container, array $config, XmlFileLoader $loader):void {
    $loader->load('metadata/metadata.xml');
    $loader->load('metadata/xml.xml');
    [$xmlResources, $yamlResources] = $this->getResourcesToWatch($container, $config);

    if (class_exists(Annotation::class)) {
      $loader->load('metadata/annotation.xml');
    }

  }

  final protected function processConfiguration(ConfigurationInterface $configuration, array $configs): array {
    $processor = new Processor();

    return $this->processedConfigs[] = $processor->processConfiguration($configuration, $configs);
  }

  /**
   * Registers the Swagger, ReDoc and Swagger UI configuration.
   */
  private function registerSwaggerConfiguration(ContainerBuilder $container, array $config, XmlFileLoader $loader) {
    if (!$config['enable_swagger']) {
      return;
    }

    $loader->load('swagger.xml');

    if ($config['enable_swagger_ui'] || $config['enable_re_doc']) {
      $loader->load('swagger-ui.xml');
      $container->setParameter('api_platform.enable_swagger_ui', $config['enable_swagger_ui']);
      $container->setParameter('api_platform.enable_re_doc', $config['enable_re_doc']);
    }
    $container->setParameter('api_platform.enable_swagger', $config['enable_swagger']);
    $container->setParameter('api_platform.swagger.api_keys', $config['swagger']['api_keys']);
  }

  private function registerOAuthConfiguration(ContainerBuilder $container, array $config): void
  {
    if (!$config['oauth']) {
      return;
    }

    $container->setParameter('api_platform.oauth.enabled', $this->isConfigEnabled($container, $config['oauth']));
    $container->setParameter('api_platform.oauth.client_id', $config['oauth']['client_id']);
    $container->setParameter('api_platform.oauth.client_secret', $config['oauth']['client_secret']);
    $container->setParameter('api_platform.oauth.type', $config['oauth']['type']);
    $container->setParameter('api_platform.oauth.flow', $config['oauth']['flow']);
    $container->setParameter('api_platform.oauth.token_url', $config['oauth']['token_url']);
    $container->setParameter('api_platform.oauth.authorization_url', $config['oauth']['authorization_url']);
    $container->setParameter('api_platform.oauth.scopes', $config['oauth']['scopes']);
  }

  private function registerGraphQlConfiguration(ContainerBuilder $container, array $config, XmlFileLoader $loader): void {
    $enabled = false;
    $container->setParameter('api_platform.graphql.enabled', $enabled);
  }

  /**
   * Normalizes the format from config to the one accepted by Symfony HttpFoundation.
   */
  private function getFormats(array $configFormats): array {
    $formats = [];
    foreach ($configFormats as $format => $value) {
      foreach ($value['mime_types'] as $mimeType) {
        $formats[$format][] = $mimeType;
      }
    }

    return $formats;
  }

  /**
   * @return bool Whether the configuration is enabled
   *
   * @throws InvalidArgumentException When the config is not enableable
   */
  protected function isConfigEnabled(ContainerBuilder $container, array $config)
  {
    if (!\array_key_exists('enabled', $config)) {
      throw new InvalidArgumentException("The config array has no 'enabled' key.");
    }

    return (bool) $container->getParameterBag()->resolveValue($config['enabled']);
  }

  private function registerJsonLdHydraConfiguration(ContainerBuilder $container, array $formats, XmlFileLoader $loader, bool $docEnabled): void
  {
    if (!isset($formats['jsonld'])) {
      return;
    }

    $loader->load('jsonld.xml');
    $loader->load('hydra.xml');
  }

  private function registerCacheConfiguration(ContainerBuilder $container, array $config)
  {
    $version = new Parameter('container.build_id');
    $container->getDefinition('cache.system.recorder_inner')->replaceArgument(2, $version);
  }

  private function getResourcesToWatch(ContainerBuilder $container, array $config) {
    $paths = array_unique(array_merge($config['mapping']['paths'], $this->getBundlesResourcesPaths($container, $config)));

    $resources = ['yml' => [], 'xml' => [], 'dir' => []];

    foreach ($paths as $path) {
      if (is_dir($path)) {
        $resources['dir'][] = $path;
        $container->addResource(new DirectoryResource($path, '/\.(xml|ya?ml|php)$/'));

        continue;
      }
    }

    $container->setParameter('api_platform.resource_class_directories', $resources['dir']);

    return [$resources['xml'], $resources['yml']];
  }

  private function getBundlesResourcesPaths(ContainerBuilder $container, array $config) {
    $bundlesResourcesPaths = [];

    foreach ($container->getParameter('container.namespaces') as $dirname) {
      $paths = [];
      $paths[] = "$dirname/Entity";

      foreach ($paths as $path) {
        if ($container->fileExists($path, false)) {
          $bundlesResourcesPaths[] = $path;
        }
      }
    }

    // @todo To be removed so that all modules are read.
//    $bundlesResourcesPaths = ['modules/custom/api_platform/src/Entity'];
    $bundlesResourcesPaths = ['core/modules/taxonomy/src/Entity'];

    return $bundlesResourcesPaths;
  }

  private function registerJsonProblemConfiguration(array $errorFormats, XmlFileLoader $loader): void
  {
    if (!isset($errorFormats['jsonproblem'])) {
      return;
    }

    $loader->load('problem.xml');
  }

  private function registerDoctrineOrmConfiguration(
    ContainerBuilder $container,
    array $config,
    XmlFileLoader $loader
  ) {
//    if (!$this->isConfigEnabled($container, $config['doctrine'])) {
//      return;
//    }

    $loader->load('doctrine_orm.xml');

  }

}
