<?php

    require_once('init.php');

    if (!isset($_COOKIE['user']) || !$oSession->validate($_COOKIE['user'])) {
        header('Location: http://www.last.fm/api/auth/?api_key=' . $oCall->getApiKey());
        exit;
    }

    $user = $oSession->validate($_COOKIE['user']);
    $aUser = $oSession->getUserByName($user);

    if (!$aUser || isset($_GET['logout'])) {
        setcookie('user', '');
        header('Location: index.php');
        exit;
    }

?>
<html>
    <head>
        <title>Setlist Scrobbler</title>
        <style type="text/css">
            body {
                font-family: Helvetica, Bitstream Vera Sans, sans-serif;
                color: #000000;
            }
            .content {
                text-align: left;
                margin-left: 20%;
                margin-top: 8%;
            }
            .heading {
                font-size: 3em;
                font-weight: bold;
            }
            A:link {
                text-decoration: none;
                color: #008000;
            }
            A:visited {
                text-decoration: none;
                color: #008000;
            }
            A:active {
                text-decoration: none;
                color: #008000;
            }
            A:hover {
                text-decoration: underline;
                color: #008000;
            }
        </style>
    </head>
    <body>
        <div><a href="?logout=1">Not <?=htmlentities($aUser['name'])?>?</a></div>
        <div class="content">
<?
            $oSession->setUserEnabled($user, $_GET['enabled'] );
            $aUser = $oSession->getUserByName($user);
?>
            <div class="heading">Setlist Scrobbler</div>
            <div class="item">
                <form>
                    Enabled:<br>
                    <input type="radio" name="enabled" value="true" checked> Yes
					<input type="radio" name="enabled" value="false"> No
                    <input type="submit" value="Save">
                </form>
            </div>
        </div>
    </body>
</html>
<?


