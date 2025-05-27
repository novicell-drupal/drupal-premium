<?php

$platformsh = new \Platformsh\ConfigReader\Config();
if (!$platformsh->inRuntime()) {
  return;
}

// The following block adds a $sites[] entry for each subdomain that is defined
// in routes.yaml.
foreach ($platformsh->getUpstreamRoutes($platformsh->applicationName) as $route) {
  $host = parse_url($route['url'], PHP_URL_HOST);
  if ($host !== FALSE) {
    $sites[$host] = 'DOMAIN_NAME';
  }
}
