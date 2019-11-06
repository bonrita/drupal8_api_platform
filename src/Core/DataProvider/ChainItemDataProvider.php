<?php

declare(strict_types=1);

namespace Drupal\api_platform\Core\DataProvider;

use Drupal\api_platform\Core\DataProvider\ItemDataProviderInterface;
use Drupal\api_platform\Core\Exception\ResourceClassNotFoundException;
use Drupal\api_platform\Core\Exception\ResourceClassNotSupportedException;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

/**
 * Tries each configured data provider and returns the result of the first able
 * to handle the resource class.
 */
final class ChainItemDataProvider implements ItemDataProviderInterface {

  use ContainerAwareTrait;

  /**
   * @var iterable<ItemDataProviderInterface>
   *
   * @internal
   */
  protected $dataProviders;

  //  /**
  //   * @param ItemDataProviderInterface[] $dataProviders
  //   */
  //  public function __construct(iterable $dataProviders)
  //  {
  //    $this->dataProviders = $dataProviders;
  //  }

  /**
   * @inheritDoc
   */
  public function getItem(
    string $resourceClass,
    $id,
    string $operationName = NULL,
    array $context = []
  ) {

    foreach ($this->dataProviders as $dataProvider) {
      try {
        if ($dataProvider instanceof RestrictedDataProviderInterface &&
          !$dataProvider->supports($resourceClass, $operationName, $context)) {
          continue;
        }

        return $dataProvider->getItem($resourceClass, $id, $operationName, $context);
      } catch (ResourceClassNotFoundException $e) {
        @trigger_error(sprintf('Throwing a "%s" is deprecated in favor of implementing "%s"', \get_class($e), RestrictedDataProviderInterface::class), E_USER_DEPRECATED);
        continue;
      }
    }

    return NULL;
  }

  public function setDataProvider($id) {
    $this->dataProviders[] = $this->container->get($id);
  }

}
