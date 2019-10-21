<?php

declare(strict_types=1);

namespace Drupal\api_platform\Core\DataProvider;

use Drupal\api_platform\Core\Exception\ResourceClassNotSupportedException;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

/**
 * Tries each configured data provider and returns the result of the first able to handle the resource class.
 */
final class ChainSubresourceDataProvider implements SubresourceDataProviderInterface
{
    use ContainerAwareTrait;

    /**
     * @var iterable<SubresourceDataProviderInterface>
     *
     * @internal
     */
    public $dataProviders;

//    /**
//     * @param SubresourceDataProviderInterface[] $dataProviders
//     */
//    public function __construct(iterable $dataProviders)
//    {
//        $this->dataProviders = $dataProviders;
//    }

    /**
     * {@inheritdoc}
     */
    public function getSubresource(string $resourceClass, array $identifiers, array $context, string $operationName = null)
    {
        foreach ($this->dataProviders as $dataProvider) {
            try {
                if ($dataProvider instanceof RestrictedDataProviderInterface && !$dataProvider->supports($resourceClass, $operationName, $context)) {
                    continue;
                }

                return $dataProvider->getSubresource($resourceClass, $identifiers, $context, $operationName);
            } catch (ResourceClassNotSupportedException $e) {
                @trigger_error(sprintf('Throwing a "%s" in a data provider is deprecated in favor of implementing "%s"', ResourceClassNotSupportedException::class, RestrictedDataProviderInterface::class), E_USER_DEPRECATED);
                continue;
            }
        }

        return ($context['collection'] ?? false) ? [] : null;
    }

  public function setDataProvider($id) {
    $this->dataProviders[] = $this->container->get($id);
  }

}
