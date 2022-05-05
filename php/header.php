<?php
    session_start(); //start a session with the user, storing variables so that logins are not lost.

    require_once 'functions.php';

    $pdo = initialise_mysql_connection(MYSQL_HOST, MYSQL_DATABASE, MYSQL_USER, MYSQL_PASS);

    $C = 'constant'; //create a variable that references the constant function
    echo <<<_ECHO
    <!DOCTYPE html>

    <html lang='eng' dir='ltr'>
        <head>
            <meta charset='utf-8'  />
            <meta name='viewport' content='width=device-width, initial-scale=1'  />
            <link rel='stylesheet' href='{$C('WEBSITE_ROOT')}/_/js/jquery_mobile/jquery.mobile-1.4.5.css'  />
            <link rel='stylesheet' href='{$C('WEBSITE_ROOT')}/_/css/styles.css'  />
            <link rel='icon' href='{$C('WEBSITE_ROOT')}/data/images/favicon.ico' type='image/ico'  />
            <script type='text/javascript' src='{$C('WEBSITE_ROOT')}/_/js/OSC.js'></script>
            <script type='text/javascript' src='{$C('WEBSITE_ROOT')}/_/js/javascript.js'></script>
            <script type='text/javascript' src='{$C('WEBSITE_ROOT')}/_/js/jquery/jquery-3.6.0.js'></script>
            <script type='text/javascript' src='{$C('WEBSITE_ROOT')}/_/js/jquery_mobile/jquery.mobile-1.4.5.js'></script>
    _ECHO; //echo the header of the document, containing links to javascript files, stylesheets and other meta information

    //Get a random string to be appended to all links as a GET statement, preventing them from being loaded from the Jquery cache
    $randstr = generate_random_string();

    //Check whether the user is logged in, and which account it is done from
    list($user, $userstr, $logged_in, $logged_in_as) = check_user_status();

    //using the information from above, finish the header of the document with a personalised title, and start the body section with the logo
    echo <<<_ECHO
            <title>The Holonet Archives$userstr</title>
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
        echo <<<_ECHO
                    <div class='centre'>
                        <a class='ui-btn ui-btn-inline ui-icon-edit ui-btn-icon-left' data-transtition='slidefade' href='{$C('WEBSITE_ROOT')}/modify_unit?r=$randstr'>Modify Unit</a>
                        <a class='ui-btn ui-btn-inline ui-icon-shop ui-btn-icon-left' data-transtition='slidefade' href='{$C('WEBSITE_ROOT')}/order?r=$randstr'>Order Calc</a>
                        <a class='ui-btn ui-btn-inline ui-icon-tag ui-btn-icon-left' data-transtition='slidefade' href='{$C('WEBSITE_ROOT')}/shop_page?r=$randstr'>All Shops</a>
                        <a class='ui-btn ui-btn-inline ui-icon-bullets ui-btn-icon-left' data-transtition='slidefade' href='{$C('WEBSITE_ROOT')}/unit_lookup?r=$randstr'>Unit Search</a>
                        <a class='ui-btn ui-btn-inline ui-icon-user ui-btn-icon-left' data-transtition='slidefade' href='{$C('WEBSITE_ROOT')}/profile?r=$randstr'>Profile</a>
                        <a class='ui-btn ui-btn-inline ui-icon-action ui-btn-icon-left' data-transtition='slidefade' href='{$C('WEBSITE_ROOT')}/profile/log_out.php?r=$randstr'>Log out</a>
                        <a class='ui-btn ui-btn-inline ui-icon-comment ui-btn-icon-left' data-transtition='slidefade' href='{$C('WEBSITE_ROOT')}/help/?r=$randstr'>Help</a>
                    </div>
        _ECHO;
    }
    else {//If they are not logged in, instead display a different list of options, including links to home, order calculator, unit search, sign up and login page
        echo <<<_ECHO
                    <div class='centre'>
                        <a class='ui-btn ui-btn-inline ui-icon-shop ui-btn-icon-left' data-transtition='slidefade' href='{$C('WEBSITE_ROOT')}/order?r=$randstr'>Order Calc</a>
                        <a class='ui-btn ui-btn-inline ui-icon-tag ui-btn-icon-left' data-transtition='slidefade' href='{$C('WEBSITE_ROOT')}/shop_page?r=$randstr'>All Shops</a>
                        <a class='ui-btn ui-btn-inline ui-icon-bullets ui-btn-icon-left' data-transtition='slidefade' href='{$C('WEBSITE_ROOT')}/unit_lookup?r=$randstr'>Unit Search</a>
                        <a class='ui-btn ui-btn-inline ui-icon-plus ui-btn-icon-left' data-transtition='slidefade' href='{$C('WEBSITE_ROOT')}/profile/sign_up.php?r=$randstr'>Sign Up</a>
                        <a class='ui-btn ui-btn-inline ui-icon-check ui-btn-icon-left' data-transtition='slidefade' href='{$C('WEBSITE_ROOT')}/profile/login.php?r=$randstr'>Login</a>
                        <a class='ui-btn ui-btn-inline ui-icon-comment ui-btn-icon-left' data-transtition='slidefade' href='{$C('WEBSITE_ROOT')}/help/?r=$randstr'>Help</a>
                    </div>
        _ECHO;
    }