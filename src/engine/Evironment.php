<?php
/**
 * Class Environment Handle environment settings and initialization of index script
 *
 * @author sebsauvage, sarlam <sarlam@minet.net>
 * @version 0.0.1
 * @package Shaarli\Engine
 */

namespace Shaarli\Engine;

require_once "installationLib.php";

class Environment
{
    /** @var array environment variables (use define) editable via @see loadEnvVariables */
    protected static $env = Array(
        'shaarli_version' => '0.0.5',
        'PHPPREFIX' => '<?php /* ', // Prefix to encapsulate data in php code.
        'PHPSUFFIX' => ' */ ?>', // Suffix to encapsulate data in php code.
        'WEB_PATH' => '',
        'INACTIVITY_TIMEOUT' => 3600, // (in seconds). If the user does not access any page within this time, his/her session is considered expired.
        'STAY_SIGNED_IN_TOKEN' => '',
        'MIN_PHP_VERSION' => '5.1.0',
        'DEBUG' => true,
    );

    /** @var array PHP variables (use ini_set) */
    protected static $nativesSettings = Array(
        // Set session parameters on server side.
        'session.use_cookies' => 1,       // Use cookies to store session.
        'session.use_only_cookies' => 1,  // Force cookies for session (phpsessionID forbidden in URL)
        'session.use_trans_sid' => false, // Prevent php to use sessionID in URL if cookies are disabled.

        // PHP Settings
        'max_input_time' => '60',  // High execution time in case of problematic imports/exports.
        'memory_limit' => '128M',  // Try to set max upload file size and read (May not work on some hosts).
        'post_max_size' => '16M',
        'upload_max_filesize' => '16M',
    );

    /** @var array directories to test and create necessary to work (use mkdir if doesn't exist) */
    private $directories = Array(
        'tmp',
    );

    private $rainTPL_config = array(
        "tpl_dir" => "themes/classic/tpl/",
        "cache_dir" => "tmp/"
    );

    /**
     * Construct function of environment.
     * it initialize the environment for Shaarli
     */
    public function __construct()
    {
        // NEVER TRUST IN PHP.INI
        // Some hosts do not define a default timezone in php.ini,
        // so we have to do this for avoid the strict standard error.
        date_default_timezone_set('UTC');

        if (static::$env['DEBUG']) {
            error_reporting(E_ALL ^ E_WARNING);  // See all error except warnings.
        }

        $this->checkEnvironment();

        ob_start();  // Output buffering for the page cache.

        foreach (static::$env as $key => $value) {
            define($key, $value);
        }

        foreach (static::$nativesSettings as $key => $value) {
            ini_set($key, $value);
        }

        // Force cookie path (but do not change lifetime)
        $cookie = session_get_cookie_params();
        static::setCookieParams($cookie['lifetime']);

        $this->initSession();

        $this->writeHeaders();

        // Run config screen if first run:
        if (!is_file($GLOBALS['config']['CONFIG_FILE'])) install();

        // http://server.com/x/shaarli --> /shaarli/
        static::$env['WEB_PATH'] = substr($_SERVER["REQUEST_URI"], 0, 1 + strrpos($_SERVER["REQUEST_URI"], '/', 0));

        // a token depending of deployment salt, user password, and the current ip
        static::$env['STAY_SIGNED_IN_TOKEN'] = sha1($GLOBALS['hash'] . $_SERVER["REMOTE_ADDR"] . $GLOBALS['salt']);

        $this->autoLocale();

        require $GLOBALS['config']['CONFIG_FILE'];  // Read login/password hash into $GLOBALS.

        // ------------------------------------------------------------------------------------------
        // Brute force protection system
        // Several consecutive failed logins will ban the IP address for 30 minutes.
        if (!is_file($GLOBALS['config']['IPBANS_FILENAME'])) file_put_contents($GLOBALS['config']['IPBANS_FILENAME'], "<?php\n\$GLOBALS['IPBANS']=" . var_export(array('FAILURES' => array(), 'BANS' => array()), true) . ";\n?>");
        include $GLOBALS['config']['IPBANS_FILENAME'];
    }

    /**
     * Sniff browser language to display dates in the right format automatically.
     * (Note that is may not work on your server if the corresponding local is not installed.)
     */
    private function autoLocale()
    {
        $loc = 'en_US'; // Default if browser does not send HTTP_ACCEPT_LANGUAGE
        if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) // eg. "fr,fr-fr;q=0.8,en;q=0.5,en-us;q=0.3"
        {   // (It's a bit crude, but it works very well. Prefered language is always presented first.)
            if (preg_match('/([a-z]{2}(-[a-z]{2})?)/i', $_SERVER['HTTP_ACCEPT_LANGUAGE'], $matches)) $loc = $matches[1];
        }
        setlocale(LC_TIME, $loc);  // LC_TIME = Set local for date/time format only.
    }

    /**
     * check the directory environment
     * test and create necessary path
     */
    private function checkEnvironment()
    {

        if (!is_writable(realpath(dirname(__FILE__)))) die('<pre>ERROR: Shaarli does not have the right to write in its own directory (' . realpath(dirname(__FILE__)) . ').</pre>');
        $this->directories[] = $GLOBALS['config']['DATADIR'];


        if (!is_file($GLOBALS['config']['DATADIR'] . '/.htaccess')) {
            file_put_contents($GLOBALS['config']['DATADIR'] . '/.htaccess', "Allow from none\nDeny from all\n");
        } // Protect data files.

        // Second check to see if Shaarli can write in its directory, because on some hosts is_writable() is not reliable.
        if (!is_file($GLOBALS['config']['DATADIR'] . '/.htaccess')) die('<pre>ERROR: Shaarli does not have the right to write in its data directory (' . realpath($GLOBALS['config']['DATADIR']) . ').</pre>');

        //check filesystem requirement
        foreach ($this->directories as $dir) {
            if (!is_dir($dir)) {
                mkdir($dir, 0705);
                chmod($dir, 0705);
            }
        }

        if ($GLOBALS['config']['ENABLE_LOCALCACHE']) {
            if (!is_dir($GLOBALS['config']['CACHEDIR'])) {
                mkdir($GLOBALS['config']['CACHEDIR'], 0705);
                chmod($GLOBALS['config']['CACHEDIR'], 0705);
            }
            if (!is_file($GLOBALS['config']['CACHEDIR'] . '/.htaccess')) {
                file_put_contents($GLOBALS['config']['CACHEDIR'] . '/.htaccess', "Allow from none\nDeny from all\n");
            } // Protect data files.
        }


        // In case stupid admin has left magic_quotes enabled in php.ini:
        if (get_magic_quotes_gpc()) {
            function stripslashes_deep($value)
            {
                $value = is_array($value) ? array_map('stripslashes_deep', $value) : stripslashes($value);
                return $value;
            }

            $_POST = array_map('stripslashes_deep', $_POST);
            $_GET = array_map('stripslashes_deep', $_GET);
            $_COOKIE = array_map('stripslashes_deep', $_COOKIE);
        }

        // Handling of old config file which do not have the new parameters.
        if (empty($GLOBALS['title'])) $GLOBALS['title'] = 'Shared links on ' . htmlspecialchars(indexUrl());
        if (empty($GLOBALS['timezone'])) $GLOBALS['timezone'] = date_default_timezone_get();
        if (empty($GLOBALS['redirector'])) $GLOBALS['redirector'] = '';
        if (empty($GLOBALS['disablesessionprotection'])) $GLOBALS['disablesessionprotection'] = false;
        if (empty($GLOBALS['disablejquery'])) $GLOBALS['disablejquery'] = false;
        if (empty($GLOBALS['privateLinkByDefault'])) $GLOBALS['privateLinkByDefault'] = false;
        // I really need to rewrite Shaarli with a proper configuation manager.


        //check php version
        if (version_compare(PHP_VERSION, static::$env['MIN_PHP_VERSION']) < 0) {
            header('Content-Type: text/plain; charset=utf-8');
            echo 'Your server supports php ' . PHP_VERSION . '. Shaarli requires at least php 5.1.0, and thus cannot run. Sorry.';
            exit;
        }
    }

    /**
     * Write response headers
     */
    private function writeHeaders()
    {
        // Prevent caching on client side or proxy: (yes, it's ugly)
        header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
        header("Cache-Control: no-store, no-cache, must-revalidate");
        header("Cache-Control: post-check=0, pre-check=0", false);
        header("Pragma: no-cache");
        header('Content-Type: text/html; charset=utf-8'); // We use UTF-8 for proper international characters handling.
    }

    /**
     * handle session naming and initialization
     */
    private function initSession()
    {
        session_name('shaarli');
        if (session_id() == '') session_start();  // Start session if needed (Some server auto-start sessions).
    }

    /**
     * @param $param
     */
    static function setCookieParams($param)
    {
        $cookiedir = '';
        if (dirname($_SERVER['SCRIPT_NAME']) != '/') $cookiedir = dirname($_SERVER["SCRIPT_NAME"]) . '/';
        session_set_cookie_params($param, $cookiedir, $_SERVER['HTTP_HOST']); // Set default cookie expiration and path.

    }

    public function getTPLConf()
    {
        return $this->rainTPL_config;
    }

    /**
     * load Shaarli Environment variables
     *
     * @param $setting Array/String Array of key => value environment variables to load or a key
     * @param $value String in case of setting as a key, the associated value
     */
    static function loadEnvVariables($setting, $value = null)
    {
        if (is_array($setting))
            foreach ($setting as $key => $value)
                static::loadEnvVariables($key, $value);
        else if (isset(static::$env[$setting])) {
            static::$env[$setting] = $value;

            static::$env['checksum'][$setting] = $value; // take trace of all config
        }
    }
}