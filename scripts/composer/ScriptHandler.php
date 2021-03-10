<?php
namespace Premium\composer;

use Composer\Factory;
use Composer\Package\Link;
use Composer\Script\Event;
use Composer\Semver\Constraint\Constraint;
use Composer\Semver\Constraint\MultiConstraint;

class ScriptHandler {
  /**
   * List of optional installations with list of composer packages and versions
   *
   * @var string[][][]
   */
  private static $optional_modules = [
    'Cookiebot' => [
      [
        'package' => 'drupal/cookiebot',
        'operator' => '^',
        'version' => '1.0.0-alpha8'
      ]
    ],
    'GTM' => [
      [
        'package' => 'drupal/gtm',
        'operator' => '^',
        'version' => '1.6'
      ]
    ],
    'Media bulk upload' => [
      [
        'package' => 'drupal/cookiebot',
        'operator' => '^',
        'version' => '1.0.0-alpha8'
      ]
    ]
  ];

  private static $deployment_options = [
    'Deployer' => [
      'require-dev' => [
        [
          'package' => 'deployer/deployer',
          'operator' => '^',
          'version' => '6.8'
        ],
        [
          'package' => 'deployer/recipes',
          'operator' => '^',
          'version' => '6.2'
        ]
      ],
      'dirs' => [
        'deployer',
        'deployer/recipe'
      ],
      'copy' => [
        'examples/hosting/deployer/config.yml'                  => 'deployer/config.yml',
        'examples/hosting/deployer/recipe/base.php'             => 'deployer/recipe/base.php',
        'examples/hosting/deployer/recipe/composer.php'         => 'deployer/recipe/composer.php',
        'examples/hosting/deployer/recipe/database_backup.php'  => 'deployer/recipe/database_backup.php',
        'examples/hosting/deployer/recipe/drupal_updates.php'   => 'deployer/recipe/drupal_updates.php',
        'examples/hosting/deployer/recipe/file_permissions.php' => 'deployer/recipe/file_permissions.php',
        'examples/hosting/deployer/recipe/maintenance_mode.php' => 'deployer/recipe/maintenance_mode.php',
        'examples/hosting/deployer/recipe/npm.php'              => 'deployer/recipe/npm.php',
        'examples/hosting/deployer/recipe/slack.php'            => 'deployer/recipe/slack.php'
      ],
      'token_replace' => [
        'deployer/config.yml'
      ]
    ]
  ];

  /**
   * Array of files in subtheme that needs renaming and token replacement
   *
   * @var string[]
   */
  private static $theme_files = [
    '.info',
    '.theme',
    '.atoms.yml',
    '.breakpoints.yml',
    '.key_value.yml',
    '.libraries.yml'
  ];

  /**
   * Array of files in site configuration that needs token replacement
   *
   * @var string[]
   */
  private static $configuration_files = [
    'webroot/sites/sites.php',
    'drush/drush.yml',
    'drush/drushrc.php',
    'assets/robots-additions.txt'
  ];

  /**
   * Steps done after recipe has been installed but before composer packages have been installed.
   *
   * @param \Composer\Script\Event $event
   *   Composer event.
   */
  public static function postRootPackageInstall(Event $event) {
    $in_ddev = (getenv('IS_DDEV_PROJECT') == 'true');
    $deployment_steps = [];
    $environment = [];
    if (!empty($project_name = $event->getIO()->ask('Project name:'))) {
      $environment['PROJECT_NAME'] = $project_name;
    }
    if (!empty($domain_name = $event->getIO()->ask('Domain name:'))) {
      $environment['DOMAIN_NAME'] = $domain_name;
    }
    if ($modules = $event->getIO()->select('Optional modules', array_keys(self::$optional_modules), 'none', FALSE, 'Value "%s" is invalid', TRUE)) {
    }
    if ($deployment = $event->getIO()->select('Deployment method', array_keys(self::$deployment_options), 0, FALSE, 'Value "%s" is invalid', FALSE)) {
      $deployment_steps = array_values(self::$deployment_options)[$deployment];
    }

    if (!$in_ddev) {
      if (!empty($db_host = $event->getIO()->ask('Database host:'))) {
        $environment['DB_HOST'] = $db_host;
      }
      if (!empty($db_port = $event->getIO()->ask('Database port:', '3306'))) {
        $environment['DB_PORT'] = $db_port;
      }
      if (!empty($db_name = $event->getIO()->ask('Database name:', $project_name))) {
        $environment['DB_NAME'] = $db_name;
      }
      if (!empty($db_user = $event->getIO()->ask('Database user:', $project_name))) {
        $environment['DB_USER'] = $db_user;
      }
      if (!empty($db_pass = $event->getIO()->askAndHideAnswer('Database password:'))) {
        $environment['DB_PASS'] = $db_pass;
      }
    } else {
      $environment['DB_PORT'] = getenv('DDEV_HOST_DB_PORT');
    }

    /*$project_name = 'test';
    $domain_name = 'test.dk';
    $modules = [0];
    $deployment = 0;*/

    // Add optional modules to composer.json and current running instance
    $event->getIO()->write('Adding optional modules to composer.json...');
    $json_file = Factory::getComposerFile();
    $json = json_decode(file_get_contents($json_file));
    $links = $event->getComposer()->getPackage()->getRequires();
    foreach ($modules as $choice) {
      $packages = array_values(self::$optional_modules)[$choice];
      foreach ($packages as $requirement) {
        $links[] = self::createComposerLink($event, $requirement['package'], $requirement['operator'], $requirement['version']);
        $package = $requirement['package'];
        $json->require->$package = $requirement['operator'] . $requirement['version'];
      }
    }
    var_dump($links);
    $event->getComposer()->getPackage()->setRequires($links);
    file_put_contents($json_file, str_replace('\/', '/', json_encode($json, JSON_PRETTY_PRINT)));

    // Writing environment file
    $event->getIO()->write('Writing environment file...');
    $file = '';
    foreach ($environment as $key => $value) {
      $file .= $key . '=' . $value . "\n";
      putenv($key . '=' . $value);
    }
    file_put_contents('.env', $file);

    // Renaming directories
    $event->getIO()->write('Renaming directories...');
    $site_dir = 'webroot/sites/' . $domain_name;
    rename('webroot/sites/DOMAIN_NAME/themes/custom/PROJECT_NAME', 'webroot/sites/DOMAIN_NAME/themes/custom/' . $project_name);
    rename('webroot/sites/DOMAIN_NAME', $site_dir);

    // Preparing site configuration
    $event->getIO()->write('Preparing site configuration...');
    self::replaceAllTokensInFile('webroot/sites/' . $domain_name . '/settings.php', $environment);
    foreach (self::$configuration_files as $filename) {
      self::replaceAllTokensInFile($filename, $environment);
    }

    // Preparing deployment method
    $event->getIO()->write('Preparing deployment method...');
    foreach ($deployment_steps['dirs'] as $directory) {
      mkdir($directory);
    }
    foreach ($deployment_steps['copy'] as $source => $destination) {
      $file = file_get_contents($source);
      file_put_contents($destination, $file);
    }
    foreach ($deployment_steps['copy'] as $source => $destination) {
      $file = file_get_contents($source);
      file_put_contents($destination, $file);
    }
    foreach ($deployment_steps['token_replace'] as $filename) {
      self::replaceAllTokensInFile($filename, $environment);
    }

    // Renaming files in subtheme and replacing token in subtheme files with actual project name
    $event->getIO()->write('Preparing "%PROJECT_NAME" subtheme...', ['%PROJECT_NAME' => $project_name]);
    $theme_dir = 'webroot/sites/' . $domain_name . '/themes/custom/' . $project_name;
    foreach (self::$theme_files as $theme_file) {
      $filename = $theme_dir . '/' . $project_name . $theme_file;
      rename($theme_dir . '/PROJECT_NAME' . $theme_file, $filename);
      self::replaceAllTokensInFile($filename, $environment);
    }

    $event->getIO()->write('Installing composer packages...');
  }

  /**
   * @param string $filename
   * @param array $environment
   */
  protected static function replaceAllTokensInFile($filename, array $environment) {
    $file = file_get_contents($filename);
    $file = str_replace(array_keys($environment), array_values($environment), $file);
    file_put_contents($filename, $file);
  }

  /**
   * @param Event $event
   * @param string $package
   * @param string $operator
   * @param string $version
   * @return Link
   */
  protected static function createComposerLink(Event $event, $package, $operator, $version, $description = 'requires') {
    $prettyConstraint = $operator . $version;
    if ($operator == '=') {
      $prettyConstraint = $version;
    }
    if ($operator == '^') {
      $parts = explode('.', $version);
      $nextVersion = (intval($parts[0]) + 1) . '.0.0.0-dev';
      $upperConstraint = new Constraint('<', $nextVersion);
      $lowerConstraint = new Constraint('>=', $version);
      return new Link($event->getComposer()->getPackage()->getName(), $package, new MultiConstraint([$upperConstraint, $lowerConstraint]), $description, $prettyConstraint);
    } else {
      return new Link($event->getComposer()->getPackage()->getName(), $package, new Constraint($operator, $version), $description, $prettyConstraint);
    }
  }
}
