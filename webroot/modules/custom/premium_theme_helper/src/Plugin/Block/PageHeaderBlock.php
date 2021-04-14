<?php

namespace Drupal\premium_theme_helper\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Logger\LoggerChannelInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\TypedData\Exception\MissingDataException;
use Drupal\paragraphs\Entity\Paragraph;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Routing\Route;

/**
 * Provides a 'Hero' block.
 *
 * @Block(
 *   id = "premium_hero_block",
 *   admin_label = @Translation("Hero block"),
 *   category= @Translation("Premium")
 * )
 */
class PageHeaderBlock extends RouteEntityBaseBlock
{

  /**
   * @inheritDoc
   */
  public function build()
  {
    $build = [
      '#cache' => [
        'contexts' => ['url'],
        'tags' => []
      ]
    ];

    /** @var \Drupal\Core\Entity\ContentEntityInterface $route_entity */
    $route_entity = $this->getEntityFromRouteMatch($this->routeMatch);
    if(!empty($route_entity) && $route_entity->hasField('field_hero')) {
      $build['#cache']['tags'] += $route_entity->getCacheTagsToInvalidate();

      if (!$route_entity->get('field_hero')->isEmpty()) {
        try {
          $pid = $route_entity->get('field_hero')->first();
          if (!is_null($pid)) {
            $pid = $pid->getValue()['target_id'];
            $paragraph = Paragraph::load($pid);
            if (!is_null($paragraph)) {
              $build['hero'] = \Drupal::entityTypeManager()->getViewBuilder('paragraph')->view($paragraph);
              $build['hero']['#title'] = $route_entity->label();
              $build['#cache']['tags'] += $paragraph->getCacheTagsToInvalidate();
            }
          }
        }
        catch (MissingDataException $e) {
          $this->logger->error($e->getMessage());
        }
      }
    }

    return $build;
  }
}
