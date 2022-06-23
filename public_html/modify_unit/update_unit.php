<?php
    require_once '../../php/functions.php';
    require_once DOCUMENT_ROOT . 'php/header.php';

    if (!$logged_in) { //If the user is not logged in, display the message below.
        echo "You must be logged in to use this feature.</div>";
    } elseif (!in_array(3, $privilege)) {
        echo "You do not have the required privilege to use this feature.</div>";
    } else {
        echo "<div class='centre-div'>";

        $ingest_shops = FALSE;

        $unit_id = '';
        $unit_data = '';
        
        if (isset($_POST['unit_data']) && isset($_POST['unit_id']) && isset($_POST['no'])) {
            $unit_data = sanitise_string($pdo, $_POST['unit_data']);
            $unit_data = str_replace(['\n', '\r'], ["\n", "\r"], $unit_data);
            $unit_id = sanitise_string($pdo, $_POST['unit_id']);
        } elseif (isset($_POST['unit_data']) && isset($_POST['unit_id']) && isset($_POST['yes'])) {
            $unit_id = sanitise_string($pdo, $_POST['unit_id']);

            if (validate_id($pdo, $unit_id, 'unit') == '') {
                $unit_data = sanitise_string($pdo, $_POST['unit_data']);
                $unit_data = str_replace(array('\r', '\n'), array("\r", "\n"), $unit_data);
                
                $unit_data = explode("\n", $unit_data);

                if (isset($_POST['wiki_link'])) $unit_data[] = "Wiki Link: " . sanitise_string($pdo, $_POST['wiki_link']);
                if (isset($_POST['unit_type'])) $unit_data[] = "Unit Type: " . sanitise_string($pdo, $_POST['unit_type']);

                $unit = ingest_unit_from_data($pdo, $unit_data, $unit_id);
                $unit_data = implode("\n", $unit_data);
                update_unit($pdo, $unit_id, $unit);
                echo "<div class='centre'><h1>Unit Sucessfully Updated</h1></div>";
            } else echo "<div class='centre'><h1>Invalid Unit ID</h1></div>";
        } elseif (isset($_POST['unit_data']) && isset($_POST['unit_id'])) {
            $unit_id = sanitise_string($pdo, $_POST['unit_id']);

            if (validate_id($pdo, $unit_id, 'unit') == '') {
                $unit_data = sanitise_string($pdo, $_POST['unit_data']);
                $unit_data = str_replace(array('\r', '\n'), array("\r", "\n"), $unit_data);
                
                $unit_data = explode("\n", $unit_data);

                $ingest_shops = TRUE;

                if (isset($_POST['wiki_link'])) $unit_data[] = "Wiki Link: " . sanitise_string($pdo, $_POST['wiki_link']);
                if (isset($_POST['unit_type'])) $unit_data[] = "Unit Type: " . sanitise_string($pdo, $_POST['unit_type']);
            }
        } elseif (isset($_POST['wiki_link']) && isset($_POST['unit_id'])) {
            $unit_id = sanitise_string($pdo, $_POST['unit_id']);

            if (validate_id($pdo, $unit_id, 'unit') == '') {
                $wiki_link = sanitise_string($pdo, $_POST['wiki_link']);
                $unit_data = file_get_contents(str_replace(array('&#039;'), array("'"), $wiki_link));
                $open_aside = strpos($unit_data, '<aside');
                $close_aside = strpos($unit_data, '</aside');
                $unit_data = substr($unit_data, $open_aside, $close_aside-$open_aside);
                $unit_data = preg_replace('/<\/li>/i', "\n", $unit_data);
                $unit_data = trim(strip_tags($unit_data));

                $unit_data = preg_replace('/(\[|&#91;)([1-9][0-9]*|\S*)(\]|&#93;)/i', ' ', $unit_data);
                $unit_data = sanitise_string($pdo, $unit_data);
                $unit_data = str_replace('\n', "\n", $unit_data);

                $unit_data = str_replace("\t", '', $unit_data);
                $unit_data = explode("\n\n\n", $unit_data);

                $unit_data[] = "Wiki Link: $wiki_link";
                if (isset($_POST['unit_type'])) $unit_data[] = "Unit Type: " . sanitise_string($pdo, $_POST['unit_type']);

                $ingest_shops = TRUE;
            }
        }
        if ($ingest_shops) {
            $unit = ingest_unit_from_data($pdo, $unit_data, $unit_id);
            $unit_data = implode("\n", $unit_data);

            display_unit_stats($unit);

            echo <<<_END
            <form method='post' action=''>
            <input type='hidden' name='unit_data' value='$unit_data'  />
            <input type='hidden' name='unit_id' value='$unit_id'  />
            <label for='yes' class='ui-hidden-accessible'>Yes</label>
            <input data-role='button' type='submit' name='yes' value='Yes'  />
            <label for='no' class='ui-hidden-accessible'>No</label>
            <input data-role='button' type='submit' name='no' value='No'  />
            </form>
            _END;
        } else {
            echo "<h2>Please fill out one of the options below</h2>";

            $unit_types = generate_type_list($pdo);

            echo "<h4>Wookieepedia Ingest</h4>";
            echo "<form method='post' action=''>";
            echo "<div class='ui-field-contain'><label for='unit_id'>Unit ID</label><input type='number' pattern='[0-9]*' name='unit_id'></input></div>";
            echo "<label for='wiki_link'>Wookieepedia Link:</label>";
            echo "<input type='text' name='wiki_link'></input>";
            echo "<div class='ui-field-contain'><label for='unit_type'>Unit Type:</label><select name='unit_type'>";
            foreach ($unit_types as $key => $unit_type) {
                echo "<option value='$key'>$unit_type</option>";
            }
            echo "</select></div>";
            echo "<input type='submit' value='Submit' data-role='button'>";
            echo "</form>";

            echo "<hr  />";

            echo "<h4>Text Ingest</h4>";
            echo "<form method='post' action=''>";
            echo "<div class='ui-field-contain'><label for='unit_id'>Unit ID</label><input type='number' pattern='[0-9]*' name='unit_id' value='$unit_id'></input></div>";
            echo "<div class='ui-field-contain'><label for='unit_type'>Unit Type:</label><select name='unit_type'>";
            foreach ($unit_types as $key => $unit_type) {
                echo "<option value='$key'>$unit_type</option>";
            }
            echo "</select></div>";
            echo "<label for='unit_data'>Unit Data:</label>";
            echo "<textarea name='unit_data'>$unit_data</textarea>";
            echo "<input type='submit' value='Submit' data-role='button'>";
            echo "</form>";
        }

        echo "</div></div>";
    }

    include_once DOCUMENT_ROOT . 'php/footer.php';