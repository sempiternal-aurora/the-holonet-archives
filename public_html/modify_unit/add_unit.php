<?php
    require_once '../../php/functions.php';
    
    require_once DOCUMENT_ROOT . 'php/initialise.php';
    
    display_header($title, $randstr, $logged_in_as, $logged_in, $privilege);

    if (!$logged_in) { //If the user is not logged in, display the message below.
        echo "You must be logged in to use this feature.</div>";
    } elseif (!in_array(3, $privilege)) {
        echo "You do not have the required privilege to use this feature.</div>";
    } else {
        echo "<div class='centre centre-div'>";
        echo "<h2>Please choose an option below</h2>";

        echo "</div></div>";
    }

    include_once DOCUMENT_ROOT . 'php/footer.php';