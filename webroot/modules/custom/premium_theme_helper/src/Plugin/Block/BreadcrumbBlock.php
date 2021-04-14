<?php
namespace Drupal\premium_theme_helper\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Breadcrumb\BreadcrumbBuilderInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\paragraphs\Entity\Paragraph;
use Drupal\system\Plugin\Block\SystemBreadcrumbBlock;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Routing\Route;

/**
 * Provides a 'Breadcrumb' block.
 *
 * @Block(
 *   id = "premium_breadcrumb_block",
 *   admin_label = @Translation("Breadcrumb block"),
 *   category= @Translation("Premium")
 * )
 */
class BreadcrumbBlock extends RouteEntityBaseBlock
{

  /**
   * The breadcrumb manager.
   *
   * @var \Drupal\Core\Breadcrumb\BreadcrumbBuilderInterface
   */
  protected $breadcrumbManager;

  /**
   * Constructs a new SystemBreadcrumbBlock object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Breadcrumb\BreadcrumbBuilderInterface $breadcrumb_manager
   *   The breadcrumb manager.
   * @param \Drupal\Core\Routing\RouteMatchInterface $routeMatch
   *   The current route match.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, BreadcrumbBuilderInterface $breadcrumb_manager, RouteMatchInterface $routeMatch) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $routeMatch);
    $this->breadcrumbManager = $breadcrumb_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('breadcrumb'),
      $container->get('current_route_match')
    );
  }

  /**
   * @inheritDoc
   */
  public function build()
  {
    $build = $this->breadcrumbManager->build($this->routeMatch)->toRenderable();

    /** @var \Drupal\Core\Entity\ContentEntityInterface $route_entity */
    $route_entity = $this->getEntityFromRouteMatch($this->routeMatch);
    if(!empty($route_entity) && $route_entity->hasField('field_hide_breadcrumb')) {
      if ($route_entity->hasField('field_hide_breadcrumb') && intval($route_entity->get('field_hide_breadcrumb')->first()->getValue()['value']) === 1) {
        $build = [
          '#cache' => [
            'contexts' => ['url'],
            'tags' => []
          ]
        ];
      }
      $build['#cache']['tags'] += $route_entity->getCacheTagsToInvalidate();
    }

    return $build;
  }
}
