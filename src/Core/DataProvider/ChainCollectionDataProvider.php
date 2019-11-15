<?php

declare(strict_types=1);

namespace Drupal\api_platform\Core\DataProvider;

use Drupal\api_platform\Core\Exception\ResourceClassNotSupportedException;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

/**
 * Tries each configured data provider and returns the result of the first able to handle the resource class.
 */
final class ChainCollectionDataProvider implements ContextAwareCollectionDataProviderInterface
{

  use ContainerAwareTrait;
    /**
     * @var iterable<CollectionDataProviderInterface>
     *
     * @internal
     */
    public $dataProviders;

//    /**
//     * @param CollectionDataProviderInterface[] $dataProviders
//     */
//    public function __construct(iterable $dataProviders)
//    {FilterInterface
//        $this->dataProviders = $dataProviders;
//    }

    /**
     * {@inheritdoc}
     */
    public function getCollection(string $resourceClass, string $operationName = null, array $context = [])
    {
        foreach ($this->dataProviders as $dataProvider) {
            try {
                if ($dataProvider instanceof RestrictedDataProviderInterface
                    && !$dataProvider->supports($resourceClass, $operationName, $context)) {
                    continue;
                }

                return $dataProvider->getCollection($resourceClass, $operationName, $context);
            } catch (ResourceClassNotSupportedException $e) {
                @trigger_error(sprintf('Throwing a "%s" in a data provider is deprecated in favor of implementing "%s"', ResourceClassNotSupportedException::class, RestrictedDataProviderInterface::class), E_USER_DEPRECATED);
                continue;
            }
        }

        return [];
    }

    public function setDataProvider($id) {
      $this->dataProviders[] = $this->container->get($id);
    }

}
