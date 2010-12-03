<?php

    require_once('lib/php-last.fm-api/src/lastfm.api.php');
    require_once('lib/database.class.php');

    $oDatabase = new Database();
    $oDatabase->createDb();

    $oCall = CallerFactory::getDefaultCaller();
    $oCall->setApiKey('52e420f41b41b041830694ecc3b383b6');
    $oCall->setApiSecret('11970cc8f4b06b833e9f74d1ebeb5553');
