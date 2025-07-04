<?php
if (!isset($_ENV['PROJECT_NAME']) || empty($_ENV['PROJECT_NAME'])) {
  $dotenv = Dotenv\Dotenv::createMutable('../');
  $dotenv->load();
}
foreach ($_ENV as $key => $value) {
  putenv($key . '=' . $value);
}
$sites['LOCAL_SITE'] = 'DOMAIN_NAME';
