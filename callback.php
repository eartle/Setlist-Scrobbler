<?php

    require_once('init.php');

    if (!isset($_REQUEST['token'])) {
        print "No token supplied!\n";
        exit;
    }

    $auth = (array) $oCall->signedCall('auth.getSession', array('token' => $_REQUEST['token']));
    $oDatabase->createUser($auth['name'], $auth['key']);
    setcookie('user', $oDatabase->generate($auth['name']));
    header('Location: index.php');
