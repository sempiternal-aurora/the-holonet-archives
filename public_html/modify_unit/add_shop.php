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
        
        $shop_data = isset($_POST['shop_data']) ? str_replace(array('\r', '\n'), array("\r", "\n"), sanitise_string($pdo, $_POST['shop_data'])) : '';
        $is_special = isset($_POST['is_special']) ? sanitise_string($pdo, $_POST['is_special']) : 0;
        
        if (isset($_POST['yes'])) {
            $shop = ingest_shop_to_array($pdo, $shop_data, TRUE);
            insert_shop_into_database($pdo, $shop[0], $shop[1], $is_special);

            echo "Shop Entered Sucessfully, <a href='" . WEBSITE_ROOT . "/modify_unit/add_shop.php' data-ajax='false'>Click Here</a> to enter another";
        } elseif (!isset($_POST['no']) && isset($_POST['shop_data'])) {
            $shop = ingest_shop_to_array($pdo, $shop_data, TRUE);

            echo "<h2>Is this the correct shop?</h2>";
            
            echo "<form method='post' action=''>";
            display_shop($shop[0], $shop[1]);
            echo <<<_END
            <input type='hidden' name='shop_data' value='$shop_data'  />
            <label for='yes' class='ui-hidden-accessible'>Yes</label>
            <input data-role='button' type='submit' name='yes' value='Yes'  />
            <label for='no' class='ui-hidden-accessible'>No</label>
            <input data-role='button' type='submit' name='no' value='No'  />
            </form>
            _END;
        } else {
            echo "<h2>Please enter the shop data</h2>";

            echo "<form method='post' action=''>";
            echo "<textarea name='shop_data'>$shop_data</textarea>";
            echo "<div id='is-special-div'><label for='is_special'>Is Special Shop?</label><select name='is_special' data-inline='true' data-role='slider'><option value=0>No</option><option value=1>Yes</option></select></div>";
            echo "<input type='submit' data-role='button' data-inline='true' name='submit' value='Submit'  /></form>";
        }

        echo "</div></div>";
    }

    include_once DOCUMENT_ROOT . 'php/footer.php';