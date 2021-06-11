<?php

namespace Drupal\premium_theme_helper;

use Drupal\crop\Entity\Crop;

/**
 * FocalPoint helper.
 *
 * @package Drupal\premium_theme_helper
 */
class FocalPoint {

  /**
   * Return focal point data, x and y.
   *
   * @param string $uri
   *   Uri.
   * @param string $url
   *   Url.
   *
   * @return array
   *   X, Y and active state.
   */
  public function getXyInPercentageFocalPoint($uri, $url): array {
    $focal_point_data = ['x' => 0, 'y' => 0, 'active' => FALSE];
    if (\Drupal::hasService('focal_point.manager')) {
      $crop_type = \Drupal::config('focal_point.settings')->get('crop_type');
      /** @var \Drupal\focal_point\FocalPointManager $focal_point_manager */
      $focal_point_manager = \Drupal::service('focal_point.manager');
      $crop = Crop::findCrop($uri, $crop_type);
      if ($crop) {
        [$width, $height] = @getimagesize($url);
        if ($width && $height) {
          $anchor = $focal_point_manager->absoluteToRelative($crop->x->value, $crop->y->value, $width, $height);
          $focal_point_data['x'] = $anchor['x'];
          $focal_point_data['y'] = $anchor['y'];
          $focal_point_data['active'] = TRUE;
        }
      }
    }
    return $focal_point_data;
  }

}
