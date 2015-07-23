<?php
/**
 * simple loader
 */

//packagist
require_once "vendor/autoload.php";

//config loading
require_once "config/options.php";
require_once "config/global.php";

//load engine
require_once "engine/PageBuilder.php";
require_once "engine/PageCache.php";
require_once "engine/LinkDB.php";
require_once "engine/Evironment.php";

