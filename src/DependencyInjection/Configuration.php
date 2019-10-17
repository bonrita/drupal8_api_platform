<?php

declare(strict_types=1);

namespace Drupal\api_platform\DependencyInjection;


use Drupal\api_platform\Core\Exception\FilterValidationException;
use Drupal\api_platform\Core\Exception\InvalidArgumentException;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Exception\ExceptionInterface as SerializerExceptionInterface;

/**
 * Generates the configuration tree builder.
 *
 * @return \Symfony\Component\Config\Definition\Builder\TreeBuilder The tree builder
 */
final class Configuration implements ConfigurationInterface {

  /**
   * @inheritDoc
   */
  public function getConfigTreeBuilder() {

    if (method_exists(TreeBuilder::class, 'getRootNode')) {
      $treeBuilder = new TreeBuilder('api_platform');
      $rootNode = $treeBuilder->getRootNode();
    } else {
      $treeBuilder = new TreeBuilder();
      $rootNode = $treeBuilder->root('api_platform');
    }

    $rootNode
        ->children()
            ->scalarNode('title')
                ->info('The title of the API.')
                ->cannotBeEmpty()
                ->defaultValue('')
            ->end()
            ->scalarNode('description')
                ->info('The description of the API.')
                ->cannotBeEmpty()
                ->defaultValue('')
            ->end()
            ->scalarNode('version')
                ->info('The version of the API.')
                ->cannotBeEmpty()
                ->defaultValue('0.0.0')
            ->end()
            ->booleanNode('show_webby')->defaultTrue()->info('If true, show Webby on the documentation page')->end()
            ->booleanNode('enable_entrypoint')->defaultTrue()->info('Enable the entrypoint')->end()
            ->booleanNode('enable_docs')->defaultTrue()->info('Enable the docs')->end()
            ->booleanNode('enable_swagger')->defaultTrue()->info('Enable the Swagger documentation and export.')->end()
            ->booleanNode('enable_swagger_ui')->defaultValue(TRUE)->info('Enable Swagger UI')->end()
            ->booleanNode('enable_re_doc')->defaultValue(TRUE)->info('Enable ReDoc')->end()
            ->scalarNode('path_segment_name_generator')->defaultValue('api_platform.path_segment_name_generator.underscore')->info('Specify a path name generator to use.')->end()
            ->arrayNode('collection')
                ->addDefaultsIfNotSet()
                ->children()
                    ->scalarNode('order')->defaultValue('ASC')->info('The default order of results.')->end() // Default ORDER is required for postgresql and mysql >= 5.7 when using LIMIT/OFFSET request
                    ->scalarNode('order_parameter_name')->defaultValue('order')->cannotBeEmpty()->info('The name of the query parameter to order results.')->end()
                    ->arrayNode('pagination')
                        ->canBeDisabled()
                        ->addDefaultsIfNotSet()
                        ->children()
                            ->booleanNode('enabled')->defaultTrue()->info('To enable or disable pagination for all resource collections by default.')->end()
                            ->booleanNode('partial')->defaultFalse()->info('To enable or disable partial pagination for all resource collections by default when pagination is enabled.')->end()
                            ->booleanNode('client_enabled')->defaultFalse()->info('To allow the client to enable or disable the pagination.')->end()
                            ->booleanNode('client_items_per_page')->defaultFalse()->info('To allow the client to set the number of items per page.')->end()
                            ->booleanNode('client_partial')->defaultFalse()->info('To allow the client to enable or disable partial pagination.')->end()
                            ->integerNode('items_per_page')->defaultValue(30)->info('The default number of items per page.')->end()
                            ->integerNode('maximum_items_per_page')->defaultNull()->info('The maximum number of items per page.')->end()
                            ->scalarNode('page_parameter_name')->defaultValue('page')->cannotBeEmpty()->info('The default name of the parameter handling the page number.')->end()
                            ->scalarNode('enabled_parameter_name')->defaultValue('pagination')->cannotBeEmpty()->info('The name of the query parameter to enable or disable pagination.')->end()
                            ->scalarNode('items_per_page_parameter_name')->defaultValue('itemsPerPage')->cannotBeEmpty()->info('The name of the query parameter to set the number of items per page.')->end()
                            ->scalarNode('partial_parameter_name')->defaultValue('partial')->cannotBeEmpty()->info('The name of the query parameter to enable or disable partial pagination.')->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
            ->arrayNode('mapping')
                ->addDefaultsIfNotSet()
                ->children()
                    ->arrayNode('paths')
                        ->prototype('scalar')->end()
                    ->end()
                ->end()
            ->end()
            ->arrayNode('resource_class_directories')
                ->prototype('scalar')->end()
            ->end()
        ->end();

    $this->addOAuthSection($rootNode);
    $this->addGraphQlSection($rootNode);
    $this->addSwaggerSection($rootNode);

    $this->addExceptionToStatusSection($rootNode);

    $this->addFormatSection($rootNode, 'formats', [
      'jsonld' => ['mime_types' => ['application/ld+json']],
      'json' => ['mime_types' => ['application/json']], // Swagger support
      'html' => ['mime_types' => ['text/html']], // Swagger UI support
    ]);

    $this->addFormatSection($rootNode, 'error_formats', [
      'jsonproblem' => ['mime_types' => ['application/problem+json']],
      'jsonld' => ['mime_types' => ['application/ld+json']],
    ]);

    return $treeBuilder;
  }

  private function addFormatSection(ArrayNodeDefinition $rootNode, string $key, array $defaultValue): void {
    $rootNode
        ->children()
            ->arrayNode($key)
                ->defaultValue($defaultValue)
                ->info('The list of enabled formats. The first one will be the default.')
                ->normalizeKeys(false)
                ->useAttributeAsKey('format')
                ->beforeNormalization()
                    ->ifArray()
                    ->then(function ($v) {
                        foreach ($v as $format => $value) {
                          if (isset($value['mime_types'])) {
                            continue;
                          }

                          $v[$format] = ['mime_types' => $value];
                        }

                      return $v;
                    })
                ->end()
                ->prototype('array')
                    ->children()
                        ->arrayNode('mime_types')->prototype('scalar')->end()->end()
                    ->end()
                ->end()
            ->end()
        ->end();
  }


  private function addOAuthSection(ArrayNodeDefinition $rootNode): void {
    $rootNode
      ->children()
          ->arrayNode('oauth')
              ->canBeEnabled()
              ->addDefaultsIfNotSet()
              ->children()
                  ->scalarNode('client_id')->defaultValue('')->info('The oauth client id.')->end()
                  ->scalarNode('client_secret')->defaultValue('')->info('The oauth client secret.')->end()
                  ->scalarNode('type')->defaultValue('oauth2')->info('The oauth client secret.')->end()
                  ->scalarNode('flow')->defaultValue('application')->info('The oauth flow grant type.')->end()
                  ->scalarNode('token_url')->defaultValue('/oauth/v2/token')->info('The oauth token url.')->end()
                  ->scalarNode('authorization_url')->defaultValue('/oauth/v2/auth')->info('The oauth authentication url.')->end()
                  ->arrayNode('scopes')
                      ->prototype('scalar')->end()
                  ->end()
              ->end()
          ->end()
      ->end();
  }

  private function addGraphQlSection(ArrayNodeDefinition $rootNode): void {
    $rootNode
      ->children()
          ->arrayNode('graphql')
              ->{class_exists(GraphQL::class) ? 'canBeDisabled' : 'canBeEnabled'}()
              ->addDefaultsIfNotSet()
               ->children()
                  ->arrayNode('graphiql')
                      ->{class_exists(GraphQL::class) ? 'canBeDisabled' : 'canBeEnabled'}()
                  ->end()
              ->end()
          ->end()
      ->end();
  }

  private function addSwaggerSection(ArrayNodeDefinition $rootNode): void {
    $rootNode
      ->children()
        ->arrayNode('swagger')
          ->addDefaultsIfNotSet()
          ->children()
            ->arrayNode('api_keys')
              ->prototype('array')
                  ->children()
                  ->scalarNode('name')
                      ->info('The name of the header or query parameter containing the api key.')
                  ->end()
                  ->enumNode('type')
                      ->info('Whether the api key should be a query parameter or a header.')
                      ->values(['query', 'header'])
                  ->end()
                  ->end()
              ->end()
            ->end()
          ->end()
        ->end()
      ->end();
  }

     /**
     * @throws InvalidConfigurationException
     */
    private function addExceptionToStatusSection(ArrayNodeDefinition $rootNode): void
    {
        $rootNode
            ->children()
                ->arrayNode('exception_to_status')
                    ->defaultValue([
                        SerializerExceptionInterface::class => Response::HTTP_BAD_REQUEST,
                        InvalidArgumentException::class => Response::HTTP_BAD_REQUEST,
                        FilterValidationException::class => Response::HTTP_BAD_REQUEST,
//                        OptimisticLockException::class => Response::HTTP_CONFLICT,
                    ])
                    ->info('The list of exceptions mapped to their HTTP status code.')
                    ->normalizeKeys(false)
                    ->useAttributeAsKey('exception_class')
                    ->beforeNormalization()
                        ->ifArray()
                        ->then(function (array $exceptionToStatus) {
                            foreach ($exceptionToStatus as &$httpStatusCode) {
                                if (\is_int($httpStatusCode)) {
                                    continue;
                                }

                                if (\defined($httpStatusCodeConstant = sprintf('%s::%s', Response::class, $httpStatusCode))) {
                                    @trigger_error(sprintf('Using a string "%s" as a constant of the "%s" class is deprecated since API Platform 2.1 and will not be possible anymore in API Platform 3. Use the Symfony\'s custom YAML extension for PHP constants instead (i.e. "!php/const %s").', $httpStatusCode, Response::class, $httpStatusCodeConstant), E_USER_DEPRECATED);

                                    $httpStatusCode = \constant($httpStatusCodeConstant);
                                }
                            }

                            return $exceptionToStatus;
                        })
                    ->end()
                    ->prototype('integer')->end()
                    ->validate()
                        ->ifArray()
                        ->then(function (array $exceptionToStatus) {
                            foreach ($exceptionToStatus as $httpStatusCode) {
                                if ($httpStatusCode < 100 || $httpStatusCode >= 600) {
                                    throw new InvalidConfigurationException(sprintf('The HTTP status code "%s" is not valid.', $httpStatusCode));
                                }
                            }

                            return $exceptionToStatus;
                        })
                    ->end()
                ->end()
            ->end();
    }

}
