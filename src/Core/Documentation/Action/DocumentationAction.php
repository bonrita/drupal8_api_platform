<?php

declare(strict_types=1);

namespace Drupal\api_platform\Core\Documentation\Action;

use Drupal\api_platform\Core\Api\FormatsProviderInterface;
use Drupal\api_platform\Core\Documentation\Documentation;
use Drupal\api_platform\Core\Exception\InvalidArgumentException;
use Drupal\api_platform\Core\Metadata\Resource\Factory\ResourceNameCollectionFactoryInterface;
use Drupal\api_platform\Core\Util\RequestAttributesExtractor;
use Symfony\Component\HttpFoundation\Request;

/**
 * Generates the API documentation.
 */
final class DocumentationAction
{
    private $resourceNameCollectionFactory;
    private $title;
    private $description;
    private $version;
    private $formats = [];
    private $formatsProvider;

  /**
   * @param \Drupal\api_platform\Core\Metadata\Resource\Factory\ResourceNameCollectionFactoryInterface $resourceNameCollectionFactory
   * @param string $title
   * @param string $description
   * @param string $version
   * @param array $formatsProvider
   */
    public function __construct(ResourceNameCollectionFactoryInterface $resourceNameCollectionFactory, string $title = '', string $description = '', string $version = '', /* FormatsProviderInterface */ $formatsProvider = [])
    {
        $this->resourceNameCollectionFactory = $resourceNameCollectionFactory;
        $this->title = $title;
        $this->description = $description;
        $this->version = $version;
        if (\is_array($formatsProvider)) {
            if ($formatsProvider) {
                // Only trigger notification for non-default argument
                @trigger_error('Using an array as formats provider is deprecated since API Platform 2.3 and will not be possible anymore in API Platform 3', E_USER_DEPRECATED);
            }
            $this->formats = $formatsProvider;

            return;
        }
        if (!$formatsProvider instanceof FormatsProviderInterface) {
            throw new InvalidArgumentException(sprintf('The "$formatsProvider" argument is expected to be an implementation of the "%s" interface.', FormatsProviderInterface::class));
        }

        $this->formatsProvider = $formatsProvider;
    }

    public function __invoke(Request $request = null): Documentation
    {
        if (null !== $request) {
            $context = ['base_url' => $request->getBaseUrl(), 'spec_version' => $request->query->getInt('spec_version', 2)];
            if ($request->query->getBoolean('api_gateway')) {
                $context['api_gateway'] = true;
            }
            $request->attributes->set('_api_normalization_context', $request->attributes->get('_api_normalization_context', []) + $context);

            $attributes = RequestAttributesExtractor::extractAttributes($request);
        }
        // BC check to be removed in 3.0
        if (null !== $this->formatsProvider) {
            $this->formats = $this->formatsProvider->getFormatsFromAttributes($attributes ?? []);
        }

        return new Documentation($this->resourceNameCollectionFactory->create(), $this->title, $this->description, $this->version, $this->formats);
    }
}
