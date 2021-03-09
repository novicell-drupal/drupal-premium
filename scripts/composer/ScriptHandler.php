<?php
namespace Premium\composer;

use Composer\Factory;
use Composer\Package\Link;
use Composer\Script\Event;
use Composer\Semver\Constraint\Constraint;

class ScriptHandler {
  private static $optional_modules = [
    'Cookiebot' => ['drupal/cookiebot'],
    'GTM' => ['drupal/gtm'],
    'Media bulk upload' => ['drupal/cookiebot']
  ];
  private static $theme_files = [
    '.info',
    '.theme',
    '.atoms.yml',
    '.breakpoints.yml',
    '.key_value.yml',
    '.libraries.yml'
  ];

  /**
   * Pre install premium.
   *
   * @param \Composer\Script\Event $event
   *   Composer event.
   */
  public static function postRootPackageInstall(Event $event) {
    $information = [];
    /*if (!empty($project_name = $event->getIO()->ask('Project name:'))) {
        $information['project_name'] = $project_name;
    }
    if (!empty($domain_name = $event->getIO()->ask('Domain name:'))) {
        $information['domain_name'] = $domain_name;
    }
    if ($values = $event->getIO()->select('Optional modules', array_keys(self::$optional_modules), 'none', FALSE, 'Value "%s" is invalid', TRUE)) {
        if (is_array($values)) {
            $information['optional_modules'] = $values;
        }
    }*/
    $project_name = 'test';
    $domain_name = 'test.dk';
    $values = [0];

    // Add optional modules to composer.json and current running instance
    $json_file = Factory::getComposerFile();
    $json = json_decode(file_get_contents($json_file));
    $links = $event->getComposer()->getPackage()->getRequires();
    foreach ($values as $choice) {
      $packages = array_values(self::$optional_modules)[$choice];
      $package = 'drupal/cookiebot';
      $version = '1.0.0-alpha8';
      $links[] = new Link($event->getComposer()->getPackage()->getName(), $package, new Constraint('>=', $version), 'requires', '^' . $version);
      $json->require->$package = '^' . $version;
    }
    $event->getComposer()->getPackage()->setRequires($links);
    file_put_contents($json_file, str_replace('\/', '/', json_encode($json, JSON_PRETTY_PRINT)));

    $site_dir = 'webroot/sites/' . $domain_name;
    rename('webroot/sites/DOMAIN_NAME/themes/custom/PROJECT_NAME', 'webroot/sites/DOMAIN_NAME/themes/custom/' . $project_name);
    rename('webroot/sites/DOMAIN_NAME', $site_dir);

    // Renaming files in subtheme and replacing token in subtheme files with actual project name
    $theme_dir = 'webroot/sites/' . $domain_name . '/themes/custom/' . $project_name;
    foreach (self::$theme_files as $theme_file) {
      $filename = $theme_dir . '/' . $project_name . $theme_file;
      rename($theme_dir . '/PROJECT_NAME' . $theme_file, $filename);

      $file = file_get_contents($filename);
      $file = str_replace('PROJECT_NAME', $project_name, $file);
      file_put_contents($filename, $file);
    }
  }
}
