<?php
declare(strict_types=1);

namespace Drupal\api_platform\Core\Api;

use Drupal\api_platform\Core\Metadata\Resource\ResourceNameCollection;

/**
 * The first path you will see in the API.
 *
 */
final class Entrypoint {

  private $resourceNameCollection;

  public function __construct(ResourceNameCollection $resourceNameCollection) {
    $this->resourceNameCollection = $resourceNameCollection;
  }

  public function getResourceNameCollection(): ResourceNameCollection
  {
    return $this->resourceNameCollection;
  }

}
