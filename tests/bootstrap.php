<?php

defined("_PS_VERSION_") ? "" : define("_PS_VERSION_", 1);

include_once('AutoLoader.php');
AutoLoader::registerDirectory('tests/vendors/');

require_once "correios.php";