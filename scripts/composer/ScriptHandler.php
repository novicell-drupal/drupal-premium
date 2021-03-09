<?php
namespace Premium\composer;

use Composer\Command\RequireCommand;
use Composer\Package\Link;
use Composer\Plugin\PluginInterface;
use Composer\Script\Event;
use Composer\Semver\Constraint\Constraint;
use Composer\Semver\Constraint\MatchAllConstraint;
use Composer\Semver\Constraint\MultiConstraint;
use JsonSchema\Constraints\UndefinedConstraint;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;

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

    $links = $event->getComposer()->getPackage()->getRequires();
    $links[] = new Link('novicell/drupal-premium', 'drupal/cookiebot', new Constraint('>=', '1.0.0-alpha8'));
    $event->getComposer()->getPackage()->setRequires($links);
    foreach ($values as $choice) {
      $packages = array_values(self::$optional_modules)[$choice];
      /*$command = new RequireCommand();
      $input = new ArrayInput(array(
          'packages' => $packages,
          ),
          $command->getDefinition()
      );
      $command->addOption('no-plugins');
      $command->setComposer($event->getComposer()->);
      $command->setApplication();
      $command->run($input, new NullOutput());*/
    }

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
