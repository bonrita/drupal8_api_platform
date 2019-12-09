<?php

declare(strict_types=1);

namespace Drupal\api_platform\Core\Action;


use Drupal\Core\Entity\EntityInterface;

class PostCollectionAction {

  /**
   * @param object $data
   *
   * @return object
   */
  public function __invoke(EntityInterface $data) {
    if (NULL !== $data->id()) {
      $data->enforceIsNew(FALSE);
    }

    $data->save();
    return $data;
  }

}
