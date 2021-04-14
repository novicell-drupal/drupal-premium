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
class PageHeaderBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * Route match.
   *
   * @var RouteMatchInterface
   */
  protected $route_match;

  /**
   * Logger.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactoryInterface
   */
  protected $logger;

  /**
   * PageHeaderBlock constructor.
   *
   * @param array $configuration
   *   The plugin configuration.
   * @param string $plugin_id
   *   The plugin ID.
   * @param mixed $plugin_definition
   *   The plugin definition.
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   Route match.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger
   *   Logger.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, RouteMatchInterface $route_match, LoggerChannelFactoryInterface $logger) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->route_match = $route_match;
    $this->logger = $logger->get('premium_theme_helper');
  }

  /**
   * @inheritDoc
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    /** @var \Drupal\Core\Routing\RouteMatch $route_match */
    $route_match = $container->get('current_route_match');
    /** @var LoggerChannelFactoryInterface $logger */
    $logger = $container->get('logger.factory');
    return new static($configuration, $plugin_id, $plugin_definition, $route_match, $logger);
  }

  /**
   * @inheritDoc
   */
  public function build(): array {
    $build = [
      '#cache' => [
        'contexts' => ['url'],
        'tags' => []
      ]
    ];

    /** @var \Drupal\Core\Entity\ContentEntityInterface $route_entity */
    $route_entity = $this->getEntityFromRouteMatch($this->route_match);
    if (!is_null($route_entity) && $route_entity->hasField('field_hero')) {
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

  /**
   * Returns an entity parameter from a route match object.
   *
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   The route match.
   *
   * @return \Drupal\Core\Entity\EntityInterface|null
   *   The entity, or null if it's not an entity route.
   */
  protected function getEntityFromRouteMatch(RouteMatchInterface $route_match): ?EntityInterface {
    $route = $route_match->getRouteObject();
    if (!$route) {
      return NULL;
    }

    $entity_type_id = $this->getEntityTypeFromRoute($route);
    if ($entity_type_id) {
      return $route_match->getParameter($entity_type_id);
    }

    return NULL;
  }

  /**
   * Return the entity type id from a route object.
   *
   * @param \Symfony\Component\Routing\Route $route
   *   The route object.
   *
   * @return string|null
   *   The entity type id, null if it doesn't exist.
   */
  protected function getEntityTypeFromRoute(Route $route): ?string {
    if (!empty($route->getOptions()['parameters'])) {
      foreach ($route->getOptions()['parameters'] as $option) {
        if (isset($option['type']) && strpos($option['type'], 'entity:') === 0) {
          return substr($option['type'], strlen('entity:'));
        }
      }
    }

    return NULL;
  }
}
