<?php

    require_once('init.php');
    require_once('shared.php');

    if (!isset($_REQUEST['token'])) {
        print "No token supplied!\n";
        exit;
    }

    $auth = (array) $oCall->signedCall('auth.getSession', array('token' => $_REQUEST['token']));
    $oDatabase->createUser($auth['name'], $auth['key']);

    // the cookie will expire in 30 days
    setcookie('user', $oDatabase->generate($auth['name'], mktime(). time()+60*60*24*30, '/'));

    //$aUser = $oDatabase->getUserByName($auth['name']);
    //scrobbleUser($aUser, $oDatabase);

    header('Location: /?login=1');

?>
