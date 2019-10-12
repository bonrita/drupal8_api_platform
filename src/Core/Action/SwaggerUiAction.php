<?php

declare(strict_types=1);

namespace Drupal\api_platform\Core\Action;


use Drupal\api_platform\Core\Documentation\Documentation;
use Drupal\api_platform\Core\Util\RequestAttributesExtractor;
use Drupal\api_platform\Core\Api\FormatsProviderInterface;
use Drupal\api_platform\Core\Exception\InvalidArgumentException;
use Drupal\api_platform\Core\Metadata\Resource\Factory\ResourceMetadataFactoryInterface;
use Drupal\api_platform\Core\Metadata\Resource\Factory\ResourceNameCollectionFactoryInterface;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Render\HtmlResponse;
use Drupal\Core\Render\RenderContext;
use Drupal\Core\Render\RendererInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Twig\Environment as TwigEnvironment;

/**
 * Class SwaggerUiAction
 *
 * @package Drupal\api_platform\Core\Action
 *
 * Displays the documentation.
 */
final class SwaggerUiAction {

  private $resourceNameCollectionFactory;
  private $resourceMetadataFactory;
  private $normalizer;
  private $twig;
  private $urlGenerator;
  private $title;
  private $description;
  private $version;
  private $showWebby;
  private $formats = [];
  private $oauthEnabled;
  private $oauthClientId;
  private $oauthClientSecret;
  private $oauthType;
  private $oauthFlow;
  private $oauthTokenUrl;
  private $oauthAuthorizationUrl;
  private $oauthScopes;
  private $formatsProvider;
  private $swaggerUiEnabled;
  private $reDocEnabled;
  private $graphqlEnabled;

  /**
   * The renderer service.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

  /**
   * @throws InvalidArgumentException
   */
  public function __construct(ResourceNameCollectionFactoryInterface $resourceNameCollectionFactory, ResourceMetadataFactoryInterface $resourceMetadataFactory, NormalizerInterface $normalizer, RendererInterface $renderer, UrlGeneratorInterface $urlGenerator, string $title = '', string $description = '', string $version = '', /* FormatsProviderInterface */ $formatsProvider = [], $oauthEnabled = false, $oauthClientId = '', $oauthClientSecret = '', $oauthType = '', $oauthFlow = '', $oauthTokenUrl = '', $oauthAuthorizationUrl = '', $oauthScopes = [], bool $showWebby = true, bool $swaggerUiEnabled = false, bool $reDocEnabled = false, bool $graphqlEnabled = false)
  {
    $this->resourceNameCollectionFactory = $resourceNameCollectionFactory;
    $this->resourceMetadataFactory = $resourceMetadataFactory;
    $this->normalizer = $normalizer;
//    $this->twig = $twig;
    $this->urlGenerator = $urlGenerator;
    $this->title = $title;
    $this->showWebby = $showWebby;
    $this->description = $description;
    $this->version = $version;
    $this->oauthEnabled = $oauthEnabled;
    $this->oauthClientId = $oauthClientId;
    $this->oauthClientSecret = $oauthClientSecret;
    $this->oauthType = $oauthType;
    $this->oauthFlow = $oauthFlow;
    $this->oauthTokenUrl = $oauthTokenUrl;
    $this->oauthAuthorizationUrl = $oauthAuthorizationUrl;
    $this->oauthScopes = $oauthScopes;
    $this->swaggerUiEnabled = $swaggerUiEnabled;
    $this->reDocEnabled = $reDocEnabled;
    $this->graphqlEnabled = $graphqlEnabled;

    $this->renderer = $renderer;

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


  public function __invoke(Request $request) {

    // BC check to be removed in 3.0
    if (NULL !== $this->formatsProvider) {
      $this->formats = $this->formatsProvider->getFormatsFromAttributes(
        RequestAttributesExtractor::extractAttributes($request)
      );
    }



    $documentation = new Documentation($this->resourceNameCollectionFactory->create(), $this->title, $this->description, $this->version, $this->formats);

    // Return a response instead of a render array so that the content
    // will not have all the blocks and page elements normally rendered by
    // Drupal.
    $response = new HtmlResponse();

    $element = [
      '#theme' => 'swagger_ui_index',
      '#data' => $this->getContext($request, $documentation),
    ];

    $content = $this->renderer->executeInRenderContext(new RenderContext(), function () use ($element) {
      return $this->renderer->render($element);
    });

    $response
      ->setContent($content)
      ->addCacheableDependency(CacheableMetadata::createFromRenderArray($element));

    return $response;
  }

  /**
   * Gets the base Twig context.
   */
  private function getContext(Request $request, Documentation $documentation): array
  {
    $context = [
      'title' =>  $this->title,
      'description' => $this->description,
      'formats' => $this->formats,
      'showWebby' => $this->showWebby,
      'swaggerUiEnabled' => $this->swaggerUiEnabled,
      'reDocEnabled' => $this->reDocEnabled,
      'graphqlEnabled' => $this->graphqlEnabled,
    ];

    $swaggerContext = ['spec_version' => $request->query->getInt('spec_version', 2)];
    if ('' !== $baseUrl = $request->getBaseUrl()) {
      $swaggerContext['base_url'] = $baseUrl;
    }

    $swaggerData = [
      //      'url' => $this->urlGenerator->generate('api_doc', ['format' => 'json']),
      'spec' => $this->normalizer->normalize($documentation, 'json', $swaggerContext),
    ];

    $swaggerData['oauth'] = [
      'enabled' => $this->oauthEnabled,
      'clientId' => $this->oauthClientId,
      'clientSecret' => $this->oauthClientSecret,
      'type' => $this->oauthType,
      'flow' => $this->oauthFlow,
      'tokenUrl' => $this->oauthTokenUrl,
      'authorizationUrl' => $this->oauthAuthorizationUrl,
      'scopes' => $this->oauthScopes,
    ];

    if ($request->isMethodSafe(false) && null !== $resourceClass = $request->attributes->get('_api_resource_class')) {
      $gg =0;
    }

    return $context + ['swagger_data' => $swaggerData];
  }


}
