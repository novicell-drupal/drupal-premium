<?php
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();
$sites['LOCAL_SITE'] = 'DOMAIN_NAME';
