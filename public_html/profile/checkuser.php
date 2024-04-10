<?php
    require_once '../../php/functions.php';

    $pdo = initialise_mysql_connection(MYSQL_HOST, MYSQL_DATABASE, MYSQL_USER, MYSQL_PASS);

    if (isset($_POST['user'])) {
        $user = sanitise_string($pdo, $_POST['user']);
        $result = $pdo->query("SELECT * FROM user WHERE username='$user'");

        if ($result->rowCount()) {
            echo "<span class='taken'>&nbsp;&#x2718; " . "The username '$user' is taken </span>";
        } else {
            echo "<span class='available'>&nbsp;&#x2714; " . "The username '$user' is available </spam>";
        }
    }