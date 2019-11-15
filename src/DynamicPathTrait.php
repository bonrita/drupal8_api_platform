<?php


namespace Drupal\api_platform;


use Symfony\Component\HttpFoundation\Request;

trait DynamicPathTrait {

  private $pathEnd;

  protected function isDynamicApiPath(Request $request): bool {
    $path = $request->getPathInfo();

    $parts = explode('/', $path);
    $this->pathEnd = array_pop($parts);

    return (isset($parts[1]) && 'api' === $parts[1] && strpos($this->pathEnd, '.') !== FALSE);
  }

}
