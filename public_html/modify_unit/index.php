<?php
    require_once '../../php/functions.php';
    require_once DOCUMENT_ROOT . 'php/header.php';

    if (!$logged_in) { //If the user is not logged in, display the message below.
        echo "You must be logged in to use this feature.";
    }
    else {
        
    }