<?php
    require_once '../_/php/functions.php';
    require_once DOCUMENT_ROOT . '_/php/header.php';

    if (!$logged_in) { //If the user is not logged in, display the message below.
        echo "You must be logged in to use this feature.";
    }
    else {
        
    }