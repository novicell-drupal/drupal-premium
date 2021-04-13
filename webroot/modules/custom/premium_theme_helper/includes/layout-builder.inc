<?php

use Drupal\Component\Utility\NestedArray;
use Drupal\premium_theme_helper\Element\LayoutBuilder;
use Drupal\premium_theme_helper\Plugin\Layout\ThreeColumnLayout;
use Drupal\premium_theme_helper\Plugin\Layout\TwoColumnLayout;
use Drupal\premium_theme_helper\Plugin\Layout\OneColumnLayout;

/**
 * Layout alter - Change and remove definitions.
 *
 * @param array $definitions
 *   The discovered plugin definitions.
 */
function premium_theme_helper_layout_alter(array &$definitions) {
  $definitions['layout_onecol']->setClass(OneColumnLayout::class);
  $definitions['layout_twocol_section']->setClass(TwoColumnLayout::class);
  $definitions['layout_threecol_section']->setClass(ThreeColumnLayout::class);
}

/**
 * Add libraries to specific elements.
 *
 * @param array $types
 *   An associative array with structure identical to that of the return value
 *   of \Drupal\Core\Render\ElementInfoManagerInterface::getInfo().
 */
function premium_theme_helper_element_info_alter(array &$types) {
  if (isset($types['layout_builder'])) {
    $types['layout_builder']['#attached']['library'][] = 'premium_theme/layout-builder';
  }
}

/**
 * Change layout builder class, so we can change structure in layout builder.
 *
 * @param array $definitions
 *   The discovered plugin definitions.
 */
function premium_theme_helper_element_plugin_alter(array &$definitions) {
  if (isset($definitions['layout_builder']['class'])) {
    $definitions['layout_builder']['class'] = LayoutBuilder::class;
  }
}

/**
 * Add header.
 *
 * @param array $variables
 *   Theme variables.
 */
function premium_theme_helper_preprocess_layout(array &$variables) {
  if (!empty($variables['content']['#header'])) {
    $variables['header'] = $variables['content']['#header'];
  }
}

/**
 * Implements hook_preprocess_HOOK().
 */
function premium_theme_helper_preprocess_block__inline_block(&$variables) {
  if (isset($variables['content']['#attributes'], $variables['attributes']) && \Drupal::currentUser()->hasPermission('access in-place editing')) {
    $variables['attributes'] = NestedArray::mergeDeep($variables['content']['#attributes'], $variables['attributes']);
  }
}
