<?php

    require_once('init.php');
    require_once('shared.php');
    
    $aUsers = $oDatabase->getUsers();

    foreach ($aUsers as $aUser) {
        scrobbleUser($aUser, $oDatabase);
    }
    
    print PHP_EOL;
?>

