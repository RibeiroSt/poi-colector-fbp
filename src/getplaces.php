<?php
/**
 * Created by PhpStorm.
 * User: Renato
 * Date: 11/12/2018
 * Time: 14:32
 */

set_time_limit(120);

require_once 'autoload.php';

// --
$error_code = $_GET['error_code'];
$error_msg = $_GET['error_message'];
$code = $_GET['code'];
$state = $_GET['state'];

if (!session_id()) {

    session_start();
}
$control = (!empty($_GET['control'])) ? $_GET['control'] : 0;
$total = (!empty($_GET['total'])) ? $_GET['total'] : 0;

$main = new Main();

if (!empty($error_code)) {

    session_destroy();
    $main->printError($error_msg);
} else {

    if (!empty($code) && !empty($state)) {

        $_SESSION['FBRLH_state'] = $state;

        if (empty($_SESSION['access_token'])) {

            $_SESSION['access_token'] = $main->getAuthToken();
        }
        $main->getPlaces($_SESSION['access_token'], ($control != 0));

        define('END_REACHED', true);

        $control ++;
        redirect($control, $total, $code, $state);

    } else {

        $redirectURL = $main->getRedirectURL();
        header("Location: " . $redirectURL);
    }
}

function redirect($control = 0, $total = 0, $code = '', $state = '') {

    $sufix  = '?control=' . $control;
    $sufix .= '&total=' . $total;

    if ($code !== '') {

        $sufix .= '&code=' . $code;
    }
    if ($state !== '') {

        $sufix .= '&state=' . $state;
    }
    echo '<meta http-equiv="refresh" content="0; URL=' . Params::BASE_URL . $sufix . '">';
}

function shutdown() {

    if (!defined('END_REACHED')) {

        redirect();
    }
}
register_shutdown_function('shutdown');
