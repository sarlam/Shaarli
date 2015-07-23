<?php
/**
 * Hardcoded parameter (These parameters can be overwritten by creating the file /config/options.php)
 * Define Global configuration, you should not edit that file.
 * to change configuration, use the file @see ./options.php
 *
 * @package src\config
 */

// -----------------------------------------------------------------------------------------------
/// Data, files and subdirectories
/** App directory root */
$GLOBALS['config']['APPDIR'] = realpath(dirname(__FILE__).'/../..');

/** data subdirectory definition, here will be stored your links */
$GLOBALS['config']['DATADIR'] = 'data';

/** configuration file, this will store your password and login */
$GLOBALS['config']['CONFIG_FILE'] = $GLOBALS['config']['DATADIR'] . '/config.php';

/** Data storage file */
$GLOBALS['config']['DATASTORE'] = $GLOBALS['config']['DATADIR'] . '/datastore.php';

/** file to store updates check of Shaarli. */
$GLOBALS['config']['UPDATECHECK_FILENAME'] = $GLOBALS['config']['DATADIR'] . '/lastupdatecheck.txt';

/** Cache directory for thumbnails for SLOW services (like flickr) */
$GLOBALS['config']['CACHEDIR'] = 'cache';

/** Page cache directory */
$GLOBALS['config']['PAGECACHE'] = 'pagecache';

// -----------------------------------------------------------------------------------------------
/// Page configurations

/** default links per page */
$GLOBALS['config']['LINKS_PER_PAGE'] = 20;

// -----------------------------------------------------------------------------------------------
/// ban configuration

/** File storage for failures and bans */
$GLOBALS['config']['IPBANS_FILENAME'] = $GLOBALS['config']['DATADIR'] . '/ipbans.php';

/** Ban IP after this many failures */
$GLOBALS['config']['BAN_AFTER'] = 4;

/** Ban duration for IP address after login failures (in seconds) (1800 sec. = 30 minutes) */
$GLOBALS['config']['BAN_DURATION'] = 1800;

// -----------------------------------------------------------------------------------------------
/// various config key

/** If true, anyone can add/edit/delete links without having to login */
$GLOBALS['config']['OPEN_SHAARLI'] = false;

/** If true, the moment when links were saved are not shown to users that are not logged in */
$GLOBALS['config']['HIDE_TIMESTAMPS'] = false;

/** Enable thumbnails in links */
$GLOBALS['config']['ENABLE_THUMBNAILS'] = true;

/** Enable Shaarli to store thumbnail in a local cache. Disable to reduce webspace usage */
$GLOBALS['config']['ENABLE_LOCALCACHE'] = true;

/** PubSubHubbub support. Put an empty string to disable, or put your hub url here to enable */
$GLOBALS['config']['PUBSUBHUB_URL'] = '';

/** Updates check frequency for Shaarli. 86400 seconds=24 hours */
$GLOBALS['config']['UPDATECHECK_INTERVAL'] = 86400;

/** include optionnal option file */
if (is_file($GLOBALS['config']['APPDIR'] . '/src/config/options.php')) require($GLOBALS['config']['APPDIR'] . '/src/config/options.php');

// Note: You must have publisher.php in the same directory as Shaarli index.php
/** END OF FILE */