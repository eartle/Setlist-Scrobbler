<?php

    require_once('lib/php-last.fm-api/src/lastfm.api.php');
    require_once('lib/database.class.php');

    $limit = 10;

    $oDatabase = new Database();
    $oDatabase->createDb();

    $oCall = CallerFactory::getDefaultCaller();
    $oCall->setApiKey('52e420f41b41b041830694ecc3b383b6');
    $oCall->setApiSecret('11970cc8f4b06b833e9f74d1ebeb5553');

    $user = $oDatabase->validate($_COOKIE['user']);
    $aUser = $oDatabase->getUserByName($user);
    
    if (!$aUser || isset($_GET['logout'])) {
        setcookie('user', '', 0, '/');
    }
    
    $notLoggedIn = (!isset($_COOKIE['user']) || !$oDatabase->validate($_COOKIE['user']) || $_GET['logout'] == 1);

    $page = 0;
    if (array_key_exists('page', $_GET)) {
        $page = max(0, $_GET['page']);
    }

?>