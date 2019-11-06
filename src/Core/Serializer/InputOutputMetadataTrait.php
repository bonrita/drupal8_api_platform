<?php

declare(strict_types=1);

namespace Drupal\api_platform\Core\Serializer;

use Drupal\api_platform\Core\Exception\ResourceClassNotFoundException;
use Drupal\api_platform\Core\Metadata\Resource\Factory\ResourceMetadataFactoryInterface;

trait InputOutputMetadataTrait
{
    /**
     * @var ResourceMetadataFactoryInterface
     */
    protected $resourceMetadataFactory;

    protected function getInputClass(string $class, array $context = []): ?string
    {
        return $this->getInputOutputMetadata($class, 'input', $context);
    }

    protected function getOutputClass(string $class, array $context = []): ?string
    {
        return $this->getInputOutputMetadata($class, 'output', $context);
    }

    private function getInputOutputMetadata(string $class, string $inputOrOutput, array $context)
    {
        if (null === $this->resourceMetadataFactory || null !== ($context[$inputOrOutput]['class'] ?? null)) {
            return $context[$inputOrOutput]['class'] ?? null;
        }

        try {
            $metadata = $this->resourceMetadataFactory->create($class);
        } catch (ResourceClassNotFoundException $e) {
            return null;
        }

        return $metadata->getAttribute($inputOrOutput)['class'] ?? null;
    }
}
