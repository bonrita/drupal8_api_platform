<?php

declare(strict_types=1);

namespace Drupal\api_platform\Core\Metadata\Resource\Factory;


use Drupal\api_platform\Core\Metadata\Resource\ResourceNameCollection;

interface ResourceNameCollectionFactoryInterface {

  /**
   * Creates the resource name collection.
   */
  public function create(): ResourceNameCollection;

}
