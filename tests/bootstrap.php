<?php

$_SERVER['REQUEST_URI'] = "teste";

defined("_PS_VERSION_") ? "" : define("_PS_VERSION_", 1);
defined("_PS_CACHE_ENABLED_") ? "" : define("_PS_CACHE_ENABLED_", 0);

include_once('AutoLoader.php');
AutoLoader::registerDirectory('tests/vendors/');

require_once "correios.php";