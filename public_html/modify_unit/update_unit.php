<?php
    require_once '../../php/functions.php';
    require_once DOCUMENT_ROOT . 'php/header.php';

    foreach ($_POST as $key => $value) {
        echo "$key: $value<br  />";
    }

    if (!$logged_in) { //If the user is not logged in, display the message below.
        echo "You must be logged in to use this feature.</div>";
    } elseif (!in_array(3, $privilege)) {
        echo "You do not have the required privilege to use this feature.</div>";
    } else {
        echo "<div class='centre centre-div'>";
        
        if (isset($_POST['unit_data']) && isset($_POST['unit_id'])) {
            $unit_id = sanitise_string($pdo, $_POST['unit_id']);

            if (validate_id($pdo, $unit_id, 'unit') == '') {
                $unit_data = sanitise_string($pdo, $_POST['unit_data']);
                $unit_data = str_replace(array('\r', '\n'), array("\r", "\n"), $unit_data);
                
                $unit_data = explode("\n", $unit_data);
            }
        } elseif (isset($_POST['wiki_link']) && isset($_POST['unit_id'])) {
            $unit_id = sanitise_string($pdo, $_POST['unit_id']);

            if (validate_id($pdo, $unit_id, 'unit') == '') {
                $wiki_link = sanitise_string($pdo, $_POST['wiki_link']);
                $unit_data = file_get_contents($wiki_link);
                $open_aside = strpos($unit_data, '<aside');
                $close_aside = strpos($unit_data, '</aside');
                $unit_data = substr($unit_data, $open_aside, $close_aside-$open_aside);
                $unit_data = trim(strip_tags($unit_data));

                $unit_data = preg_replace('/(\[|&#91;)([1-9][0-9]*|\S*)(\]|&#93;)/i', ' ', $unit_data);

                $unit_data = str_replace("\t", '', $unit_data);
                $unit_data = explode("\n\n\n", $unit_data);
            }
        }
        if (isset($unit_data)) {
            $unit = ingest_unit_from_data($pdo, $unit_data, $unit_id);
        } else {
            echo "<h2>Please fill out one of the options below</h2>";

            echo "<h4>Wookieepedia Ingest</h4>";
            echo "<form method='post' action=''>";
            echo "<div class='ui-field-contain'><label for='unit_id'>Unit ID</label><input type='number' pattern='[0-9]*' name='unit_id'></input></div>";
            echo "<label for='wiki_link'>Wookieepedia Link:</label>";
            echo "<input type='text' name='wiki_link'></input>";
            echo "<input type='submit' value='Submit' data-role='button'>";
            echo "</form>";

            echo "<hr  />";

            echo "<h4>Text Ingest</h4>";
            echo "<form method='post' action=''>";
            echo "<div class='ui-field-contain'><label for='unit_id'>Unit ID</label><input type='number' pattern='[0-9]*' name='unit_id'></input></div>";
            echo "<label for='unit_data'>Unit Data:</label>";
            echo "<textarea name='unit_data'></textarea>";
            echo "<input type='submit' value='Submit' data-role='button'>";
            echo "</form>";
        }

        echo "</div></div>";
    }

    include_once DOCUMENT_ROOT . 'php/footer.php';