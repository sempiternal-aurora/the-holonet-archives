<?php
    echo <<<_ECHO
    <!DOCTYPE html>

    <html lang='eng' dir='ltr'>
        <head>
            <meta charset='utf-8'  />
            <meta name='viewport' content='width=device-width, initial-scale=1'  />
            <link rel='stylesheet' href='{$C('WEBSITE_ROOT')}/_/js/jquery_mobile/jquery.mobile-1.4.5.css'  />
            <link rel='stylesheet' href='{$C('WEBSITE_ROOT')}/_/css/holonet-original.css'  />
            <link rel='stylesheet' href='{$C('WEBSITE_ROOT')}/_/css/jquery.mobile.icons.min.css'  />
            <link rel='stylesheet' href='{$C('WEBSITE_ROOT')}/_/css/styles.css'  />
            <link rel='icon' href='{$C('WEBSITE_ROOT')}/data/images/favicon.ico' type='image/ico'  />
            <script type='text/javascript' src='{$C('WEBSITE_ROOT')}/_/js/OSC.js'></script>
            <script type='text/javascript' src='{$C('WEBSITE_ROOT')}/_/js/javascript.js'></script>
            <script type='text/javascript' src='{$C('WEBSITE_ROOT')}/_/js/jquery/jquery-2.1.4.js'></script>
            <script type='text/javascript' src='{$C('WEBSITE_ROOT')}/_/js/jquery_mobile/jquery.mobile-1.4.5.js'></script>
    _ECHO; //echo the header of the document, containing links to javascript files, stylesheets and other meta information

    if (isset($title)) {
        echo "<title>$title</title>";
    } else {
        echo "<title>The Holonet Archives$userstr</title>";
    }
    //using the information from above, finish the header of the document with a personalised title, and start the body section with the logo
    echo <<<_ECHO
        </head>
        </body>
            <div data-role='page'>
                <div data-role='header'>
                    <div class='centre'>
                        <a class='no-show' href='{$C('WEBSITE_ROOT')}?r=$randstr'>
                            <span id='logo'>The H<img height='30px' id='net' src='{$C('WEBSITE_ROOT')}/data/images/logo.png' alt='o'  />lonet Archives</span>
                        </a>
                    </div>
                    <div id='center-username' class='username'>$logged_in_as</div>
                </div>
                <div data-role='content'>
    _ECHO;

    
    if ($logged_in) {//If they are logged in, display a button linking to the home, modify unit, order calculator, unit search, profile and log out page
        echo "<div class='centre'>";

        if (in_array(3, $privilege)) {
            echo "<a data-role='button' data-inline='true' data-icon='edit' data-transtition='slidefade' href='{$C('WEBSITE_ROOT')}/modify_unit?r=$randstr'>Modify Unit</a>";
        }

        echo <<<_ECHO
                        <a data-role='button' data-inline='true' data-icon='tag' data-transtition='slidefade' href='{$C('WEBSITE_ROOT')}/shop_page?r=$randstr'>All Shops</a>
                        <a data-role='button' data-inline='true' data-icon='bullets' data-transtition='slidefade' data-ajax='false' href='{$C('WEBSITE_ROOT')}/unit_lookup?r=$randstr'>Unit Search</a>
                        <a data-role='button' data-inline='true' data-icon='action' data-transtition='slidefade' href='{$C('WEBSITE_ROOT')}/profile/log_out.php?r=$randstr'>Log out</a>
                        <a data-role='button' data-inline='true' data-icon='comment' data-transtition='slidefade' href='{$C('WEBSITE_ROOT')}/help/?r=$randstr'>Help</a>
                    </div>
        _ECHO;
    }
    else {//If they are not logged in, instead display a different list of options, including links to home, order calculator, unit search, sign up and login page
        echo <<<_ECHO
                    <div class='centre'>
                        <a data-role='button' data-inline='true' data-icon='edit' data-transtition='slidefade' href='{$C('WEBSITE_ROOT')}/shop_page?r=$randstr'>All Shops</a>
                        <a data-role='button' data-inline='true' data-icon='bullets' data-transtition='slidefade' data-ajax='false' href='{$C('WEBSITE_ROOT')}/unit_lookup?r=$randstr'>Unit Search</a>
                        <a data-role='button' data-inline='true' data-icon='plus' data-transtition='slidefade' href='{$C('WEBSITE_ROOT')}/profile/sign_up.php?r=$randstr'>Sign Up</a>
                        <a data-role='button' data-inline='true' data-icon='check' data-transtition='slidefade' href='{$C('WEBSITE_ROOT')}/profile/login.php?r=$randstr'>Login</a>
                        <a data-role='button' data-inline='true' data-icon='comment' data-transtition='slidefade' href='{$C('WEBSITE_ROOT')}/help/?r=$randstr'>Help</a>
                    </div>
        _ECHO;
    }