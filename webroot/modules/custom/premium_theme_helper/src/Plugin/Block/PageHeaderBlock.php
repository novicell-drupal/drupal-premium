<?php
namespace Drupal\premium_theme_helper\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Routing\RouteMatchInterface;
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
class PageHeaderBlock extends BlockBase implements ContainerFactoryPluginInterface
{

  /**
   * @var RouteMatchInterface
   */
  private $route_match;

  public function __construct(array $configuration, $plugin_id, $plugin_definition, RouteMatchInterface $route_match) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->route_match = $route_match;
  }

  /**
   * @inheritDoc
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    $route_match = $container->get('current_route_match');

    return new static($configuration, $plugin_id, $plugin_definition, $route_match);
  }

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
    $route_entity = $this->getEntityFromRouteMatch($this->route_match);
    if(!empty($route_entity) && $route_entity->hasField('field_hero')) {
      $build['#cache']['tags'] += $route_entity->getCacheTagsToInvalidate();

      if (!$route_entity->get('field_hero')->isEmpty()) {
        $pid = $route_entity->get('field_hero')->first()->getValue()['target_id'];
        $paragraph = Paragraph::load($pid);
        $build['hero'] = \Drupal::entityTypeManager()->getViewBuilder('paragraph')->view($paragraph);
        $build['#cache']['tags'] += $paragraph->getCacheTagsToInvalidate();
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
  protected function getEntityFromRouteMatch(RouteMatchInterface $route_match) {
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
  protected function getEntityTypeFromRoute(Route $route) {
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
