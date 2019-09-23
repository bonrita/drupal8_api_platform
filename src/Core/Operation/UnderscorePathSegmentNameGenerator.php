<?php

declare(strict_types=1);

namespace Drupal\api_platform\Core\Operation;

use Doctrine\Common\Inflector\Inflector;

final class UnderscorePathSegmentNameGenerator implements PathSegmentNameGeneratorInterface {

  /**
   * @inheritDoc
   */
  public function getSegmentName(string $name, bool $collection = TRUE): string {
    // TODO: Implement getSegmentName() method.
  }

}
