<?php
    require_once 'functions.php';

    session_start(); //start a session with the user, storing variables so that logins are not lost.

    $pdo = initialise_mysql_connection(MYSQL_HOST, MYSQL_DATABASE, MYSQL_USER, MYSQL_PASS);

    //Get a random string to be appended to all links as a GET statement, preventing them from being loaded from the Jquery cache
    $randstr = generate_random_string();

    //Check whether the user is logged in, and which account it is done from
    list($user, $userstr, $logged_in, $logged_in_as) = check_user_status();

    $privilege = $logged_in ? check_user_privilege($pdo, $user) : [];

    $title = "The Holonet Archives$userstr";

    $C = 'constant'; //create a variable that references the constant function