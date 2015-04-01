<?php
/**
 * Installation and update library
 *
 * @author sebsauvage, sarlam <sarlam@minet.net>
 * @version 0.0.1
 * @package Shaarli\Engine
 */

namespace Shaarli\Engine;

function install()
{
// On free.fr host, make sure the /sessions directory exists, otherwise login will not work.
    if (endsWith($_SERVER['HTTP_HOST'], '.free.fr') && !is_dir($_SERVER['DOCUMENT_ROOT'] . '/sessions')) mkdir($_SERVER['DOCUMENT_ROOT'] . '/sessions', 0705);


// This part makes sure sessions works correctly.
// (Because on some hosts, session.save_path may not be set correctly,
// or we may not have write access to it.)
    if (isset($_GET['test_session']) && (!isset($_SESSION) || !isset($_SESSION['session_tested']) || $_SESSION['session_tested'] != 'Working')) {   // Step 2: Check if data in session is correct.
        echo '<pre>Sessions do not seem to work correctly on your server.<br>';
        echo 'Make sure the variable session.save_path is set correctly in your php config, and that you have write access to it.<br>';
        echo 'It currently points to ' . session_save_path() . '<br><br><a href="?">Click to try again.</a></pre>';
        die;
    }
    if (!isset($_SESSION['session_tested'])) {   // Step 1 : Try to store data in session and reload page.
        $_SESSION['session_tested'] = 'Working';  // Try to set a variable in session.
        header('Location: ' . indexUrl() . '?test_session');  // Redirect to check stored data.
    }
    if (isset($_GET['test_session'])) {   // Step 3: Sessions are ok. Remove test parameter from URL.
        header('Location: ' . indexUrl());
    }


    if (!empty($_POST['setlogin']) && !empty($_POST['setpassword'])) {
        $tz = 'UTC';
        if (!empty($_POST['continent']) && !empty($_POST['city']))
            if (isTZvalid($_POST['continent'], $_POST['city']))
                $tz = $_POST['continent'] . '/' . $_POST['city'];
        $GLOBALS['timezone'] = $tz;
// Everything is ok, let's create config file.
        $GLOBALS['login'] = $_POST['setlogin'];
        $GLOBALS['salt'] = sha1(uniqid('', true) . '_' . mt_rand()); // Salt renders rainbow-tables attacks useless.
        $GLOBALS['hash'] = sha1($_POST['setpassword'] . $GLOBALS['login'] . $GLOBALS['salt']);
        $GLOBALS['title'] = (empty($_POST['title']) ? 'Shared links on ' . htmlspecialchars(indexUrl()) : $_POST['title']);
        writeConfig();
        echo '<script language="JavaScript">alert("Shaarli is now configured. Please enter your login/password and start shaaring your links !");document.location=\'?do=login\';</script>';
        exit;
    }

// Display config form:
    list($timezone_form, $timezone_js) = templateTZform();
    $timezone_html = '';
    if ($timezone_form != '') $timezone_html = '<tr><td valign="top"><b>Timezone:</b></td><td>' . $timezone_form . '</td></tr>';

    $PAGE = new PageBuilder;
    $PAGE->assign('timezone_html', $timezone_html);
    $PAGE->assign('timezone_js', $timezone_js);
    $PAGE->renderPage('install');
    exit;
}
