<?php
    require_once '../../php/functions.php';
    require_once DOCUMENT_ROOT . 'php/header.php';

    if (!$logged_in) { //If the user is not logged in, display the message below.
        echo "You must be logged in to use this feature.</div>";
    } elseif (!in_array(3, $privilege)) {
        echo "You do not have the required privilege to use this feature.</div>";
    } else {
        echo "<div class='centre centre-div'>";
        
        if (isset($_POST['unit_data'])) {

        } else {
            echo "<h2>Wookieepedia Ingest</h2>";
            echo "<form method='post' action=''>";
            echo "<label for='wiki_link'>Wookieepedia Link:</label>";
            echo "<input type='text'></input>";
            echo "<input type='submit' value='submit' data-role='button'>";
            echo "</form>";
        }

        echo "</div></div>";
    }

    include_once DOCUMENT_ROOT . 'php/footer.php';