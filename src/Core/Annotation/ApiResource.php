<?php


namespace Drupal\api_platform\Core\Annotation;

/**
 * Class ApiResource
 *
 * @package Drupal\api_platform\Core\Annotation
 *
 * @Annotation
 */
final class ApiResource {

  use AttributesHydratorTrait;

  /**
   * @var string
   */
  public $shortName;

  /**
   * @var string
   */
  public $description;

  /**
   * @var string
   */
  public $iri;

  /**
   * @var array
   */
  public $itemOperations;

  /**
   * @var array
   */
  public $collectionOperations;

  /**
   * @var array
   */
  public $subresourceOperations;

  /**
   * @var array
   */
  public $graphql;

  /**
   * @see https://github.com/Haehnchen/idea-php-annotation-plugin/issues/112
   *
   * @var string
   */
  private $accessControl;

  /**
   * @see https://github.com/Haehnchen/idea-php-annotation-plugin/issues/112
   *
   * @var string
   */
  private $accessControlMessage;

  /**
   * @see https://github.com/Haehnchen/idea-php-annotation-plugin/issues/112
   *
   * @var array
   */
  private $cacheHeaders;

  /**
   * @see https://github.com/Haehnchen/idea-php-annotation-plugin/issues/112
   *
   * @var array
   */
  private $denormalizationContext;

  /**
   * @see https://github.com/Haehnchen/idea-php-annotation-plugin/issues/112
   *
   * @var string
   */
  private $deprecationReason;

  /**
   * @see https://github.com/Haehnchen/idea-php-annotation-plugin/issues/112
   *
   * @var bool
   */
  private $elasticsearch;

  /**
   * @see https://github.com/Haehnchen/idea-php-annotation-plugin/issues/112
   *
   * @var bool
   */
  private $fetchPartial;

  /**
   * @see https://github.com/Haehnchen/idea-php-annotation-plugin/issues/112
   *
   * @var bool
   */
  private $forceEager;

  /**
   * @see https://github.com/Haehnchen/idea-php-annotation-plugin/issues/112
   *
   * @var array
   */
  private $formats;

  /**
   * @see https://github.com/Haehnchen/idea-php-annotation-plugin/issues/112
   *
   * @var string[]
   */
  private $filters;

  /**
   * @see https://github.com/Haehnchen/idea-php-annotation-plugin/issues/112
   *
   * @var string[]
   */
  private $hydraContext;

  /**
   * @see https://github.com/Haehnchen/idea-php-annotation-plugin/issues/112
   *
   * @var int
   */
  private $maximumItemsPerPage;

  /**
   * @see https://github.com/Haehnchen/idea-php-annotation-plugin/issues/112
   *
   * @var mixed
   */
  private $mercure;

  /**
   * @see https://github.com/Haehnchen/idea-php-annotation-plugin/issues/112
   *
   * @var bool|string
   */
  private $messenger;

  /**
   * @see https://github.com/Haehnchen/idea-php-annotation-plugin/issues/112
   *
   * @var array
   */
  private $normalizationContext;

  /**
   * @see https://github.com/Haehnchen/idea-php-annotation-plugin/issues/112
   *
   * @var array
   */
  private $order;

  /**
   * @see https://github.com/Haehnchen/idea-php-annotation-plugin/issues/112
   *
   * @var bool
   */
  private $paginationClientEnabled;

  /**
   * @see https://github.com/Haehnchen/idea-php-annotation-plugin/issues/112
   *
   * @var bool
   */
  private $paginationClientItemsPerPage;

  /**
   * @see https://github.com/Haehnchen/idea-php-annotation-plugin/issues/112
   *
   * @var bool
   */
  private $paginationClientPartial;

  /**
   * @see https://github.com/Haehnchen/idea-php-annotation-plugin/issues/112
   *
   * @var bool
   */
  private $paginationEnabled;

  /**
   * @see https://github.com/Haehnchen/idea-php-annotation-plugin/issues/112
   *
   * @var bool
   */
  private $paginationFetchJoinCollection;

  /**
   * @see https://github.com/Haehnchen/idea-php-annotation-plugin/issues/112
   *
   * @var int
   */
  private $paginationItemsPerPage;

  /**
   * @see https://github.com/Haehnchen/idea-php-annotation-plugin/issues/112
   *
   * @var int
   */
  private $paginationPartial;

  /**
   * @see https://github.com/Haehnchen/idea-php-annotation-plugin/issues/112
   *
   * @var string
   */
  private $routePrefix;

  /**
   * @see https://github.com/Haehnchen/idea-php-annotation-plugin/issues/112
   *
   * @var array
   */
  private $swaggerContext;

  /**
   * @see https://github.com/Haehnchen/idea-php-annotation-plugin/issues/112
   *
   * @var mixed
   */
  private $validationGroups;

  /**
   * @see https://github.com/Haehnchen/idea-php-annotation-plugin/issues/112
   *
   * @var string
   */
  private $sunset;

  /**
   * @see https://github.com/Haehnchen/idea-php-annotation-plugin/issues/112
   *
   * @var string|false
   */
  private $input;

  /**
   * @see https://github.com/Haehnchen/idea-php-annotation-plugin/issues/112
   *
   * @var string|false
   */
  private $output;

  /**
   * @see https://github.com/Haehnchen/idea-php-annotation-plugin/issues/112
   *
   * @var array
   */
  private $openapiContext;


}
