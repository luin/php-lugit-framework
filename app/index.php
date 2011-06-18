<?php

define('ROOT_URL', 'http://127.0.0.1/');

define('SALT', 'dsfaTJEIOFddsf3ff');
define('STATIC_PATH', '/static/');
define('DEBUG_MODE', true);

require '../lugit/lugit.php';

Lugit::run();