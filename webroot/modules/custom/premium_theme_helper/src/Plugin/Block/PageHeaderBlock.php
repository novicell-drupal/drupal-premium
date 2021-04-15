<?php

namespace Drupal\premium_theme_helper\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Breadcrumb\BreadcrumbBuilderInterface;
use Drupal\Core\Controller\TitleResolverInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\TypedData\Exception\MissingDataException;
use Drupal\paragraphs\Entity\Paragraph;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
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
class PageHeaderBlock extends RouteEntityBaseBlock {

  /**
   * The title resolver.
   *
   * @var \Drupal\Core\Controller\TitleResolverInterface
   */
  protected $titleResolver;

  /**
   * The current request.
   *
   * @var \Drupal\Core\Controller\TitleResolverInterface
   */
  protected $request_stack;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs a new SystemBreadcrumbBlock object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The breadcrumb manager.
   * @param \Drupal\Core\Routing\RouteMatchInterface $routeMatch
   *   The current route match.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger
   *   Logger.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, TitleResolverInterface $titleResolver, RouteMatchInterface $routeMatch, LoggerChannelFactoryInterface $logger, RequestStack $request_stack, EntityTypeManagerInterface $entityTypeManager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $routeMatch, $logger);
    $this->titleResolver = $titleResolver;
    $this->request_stack = $request_stack;
    $this->entityTypeManager = $entityTypeManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    /** @var \Drupal\Core\Routing\RouteMatchInterface $routeMatch */
    $routeMatch = $container->get('current_route_match');
    /** @var \Drupal\Core\Controller\TitleResolverInterface $titleResolver */
    $titleResolver = $container->get('title_resolver');
    /** @var \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger */
    $logger = $container->get('logger.factory');
    /** @var RequestStack $request_stack */
    $request_stack = $container->get('request_stack');
    /** @var \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager */
    $entityTypeManager = $container->get('entity_type.manager');
    return new static($configuration, $plugin_id, $plugin_definition, $titleResolver, $routeMatch, $logger, $request_stack, $entityTypeManager);
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
    $route_entity = $this->getEntityFromRouteMatch($this->routeMatch);
    if (!is_null($route_entity) && $route_entity->hasField('field_hero')) {
      $build['#cache']['tags'] += $route_entity->getCacheTagsToInvalidate();

      if (!$route_entity->get('field_hero')->isEmpty()) {
        try {
          $pid = $route_entity->get('field_hero')->first();
          if (!is_null($pid)) {
            $pid = $pid->getValue()['target_id'];
            $paragraph = Paragraph::load($pid);
            if (!is_null($paragraph)) {
              $build['hero'] = $this->entityTypeManager->getViewBuilder('paragraph')->view($paragraph);
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
    if (!isset($build['hero'])) {
      $request = $this->request_stack->getCurrentRequest();
      $title = $this->titleResolver->getTitle($request, $this->routeMatch->getRouteObject());
      $build['title'] = [
        '#theme' => 'page_title',
        '#title' => $title,
      ];
    }

    return $build;
  }

}
