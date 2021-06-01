<?php

namespace Drupal\premium_theme_helper;

use Drupal\crop\Entity\Crop;
use Drupal\focal_point\FocalPointManager;

/**
 * FocalPoint helper.
 *
 * @package Drupal\premium_theme_helper
 */
class FocalPoint {

  /**
   * Focal point manager.
   *
   * @var \Drupal\focal_point\FocalPointManager
   */
  protected $focalPointManager;

  /**
   * FocalPoint constructor.
   *
   * @param \Drupal\focal_point\FocalPointManager $focalPointManager
   *   Focal point manager.
   */
  public function __construct(FocalPointManager $focalPointManager) {
    $this->focalPointManager = $focalPointManager;
  }

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
    $crop_type = \Drupal::config('focal_point.settings')->get('crop_type');
    $crop = Crop::findCrop($uri, $crop_type);
    if ($crop) {
      [$width, $height] = @getimagesize($url);
      if ($width && $height) {
        $anchor = $this->focalPointManager->absoluteToRelative($crop->x->value, $crop->y->value, $width, $height);
        $focal_point_data['x'] = $anchor['x'];
        $focal_point_data['y'] = $anchor['y'];
        $focal_point_data['active'] = TRUE;
      }
    }
    return $focal_point_data;
  }

}
