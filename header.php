<nav class="navbar navbar-default">
    <div class="container">
        <div class="navbar-header">
            <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#nav-menu">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
            <a class="navbar-brand" href="/">Setlist Scrobbler</a>
        </div>
        <div class="collapse navbar-collapse" id="nav-menu">
            <ul class="nav navbar-nav">
                <li <?php print($activePage == 0 ? 'class="active"': '') ?> ><a href="/">Home</a></li>
                <li <?php print($activePage == 1 ? 'class="active"': '') ?> ><a href="/users/">Users</a></li>
                <li <?php print($activePage == 2 ? 'class="active"': '') ?> ><a href="/events/">Events</a></li>
            </ul>
            <ul class="nav navbar-nav navbar-right <?php print($notLoggedIn?"hidden":""); ?>">
                <li><a href="?logout=1">Logout as <?=$aUser['user_name']?></a></li>
            </ul>
        </div>
    </div>
</nav>
