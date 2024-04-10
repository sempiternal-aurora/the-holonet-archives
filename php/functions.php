<?php
    define("WEBSITE_ROOT", "http://localhost/the_holonet_archives/public_html");
    //define("DOCUMENT_ROOT", "/var/www/html/the_holonet_archives/"); //Linux document root
    define("DOCUMENT_ROOT", 'C:\xampp\htdocs\the_holonet_archives\\'); // Windows Document root for Xampp install
    define("MYSQL_USER", "php_console");
    define("MYSQL_PASS", "mysql");
    define("MYSQL_HOST", "localhost");
    define("MYSQL_DATABASE", "holonet");
    define("MYSQL_CHARSET", "utf8mb4");
    define("DISPLAY_NAMES", array(
        'type_description' => 'Unit Class',
        'alias' => 'nickname'
    ));
    //define("FILENAME_SLASH", '/'); //Normal unix slash for Linux, web and more
    //define("FILENAME_SLASH", '\\'); //Stupid windows backslash because they are special.

    class Unit {
        public $name;
        public $type_description;
        public $alias;
        public $price;
        public $modslots;
        public $uc_limit;
        public $length;
        public $height;
        public $width;
        public $hyperdrive;
        public $backup;
        public $mglt;
        public $kmh;
        public $shield;
        public $hull;
        public $sbd;
        public $ru;
        public $points;
        public $is_special;
        public $notes;
        public $armament = [];
        public $complement = [];
        public $crew = [];
        
        function __construct($name_str, $type, $alias = '', $modslots = 0, $price = 0) { //Initialise common values when creating the class
            $this->name = $name_str;
            $this->type = $type;
            $this->alias = $alias;
            $this->modslots = $modslots;
            $this->price = $price;
        }

        function add_notes($notes) { //add any notes if they exist
            $this->notes = $notes;
        }

        function add_armament($armament) { //add the armament if it exists
            $this->armament = $armament;
        }

        function add_complement($complement) { //add a complement if it exists
            $this->complement = $complement;
        }

        function add_crew($crew) { //add a crew if it exists.
            $this->crew = $crew;
        }
    }

    /*
    ISD REFERENCE FOR OBJECT
            'modslots' => $modslots,
            'unit_name' => $name,
            'type' => $type,
            'price' => $price,
            'notes' => $notes,
            'uc_limit' => $uc_limit
    */

    function create_table(&$pdo, $name, $query) {
        /* 
            A short and sweet piece of code to create a table if it doesn't exist. As the login provided to this server does not have the privilege to
            create tables on the holonet database, it cannot ever call this function except in setup_mysql.
        */
        $pdo->query("CREATE TABLE IF NOT EXISTS $name($query)"); //standard sql query using the referenced database connection
        echo "Table '$name' created or already exists <br  />";
    }

    function destroy_session_completely() {
        /*
            quick function called to delete all session data, expanding on the deletion caused by the included session_destroy function
        */
        $_SESSION = array(); //clear all session variables

        if (session_id() != "" || isset($_COOKIE[session_name()])) { //if the session has an id, or there is a cookie still in the users broswer with the session data
            setcookie(session_name(), '', time() - 2592000, '/'); //delete the cookie by issuing another one that will expire 24 hours ago
        }

        session_destroy(); //standard session destruction function built into php
    }

    function get_all_table_names(&$pdo, $database_name) {
        /*
            Gets an array of all table names from the current database, and returns it
        */
        $result = $pdo->query("SHOW Tables"); //query the database for all table names
        $tables = array(); //empty array that will contain table names
        while ($row = $result->fetch()) { //for each row of the result from the database
            $tables[] = $row["Tables_in_$database_name"]; //add the table name in it to the tables array we have
        }
        return $tables; //return all the tables as an array
    }

    function drop_all_tables(&$pdo, $database_name) {
        /*
            WARNING, THIS FUNCTION REALLY DELETES ALL TABLES FROM THE DATABASE GIVEN, YOU HAVE BEEN WARNED
        */
        $tables = get_all_table_names($pdo, $database_name); //Get all tables in the database
        echo "Disabling checks for foreign keys<br  />"; 
        $pdo->query("SET FOREIGN_KEY_CHECKS = 0"); //Ignore checks to see if deleting the tables will break foreign keys
        foreach ($tables as $table) { //Iterate through all the tables
            echo "Dropping $table<br  />"; 
            $pdo->query("DROP TABLE IF EXISTS $table"); //drop the table
        }
        echo "Enabling checks for foreign keys<br  />";
        $pdo->query("SET FOREIGN_KEY_CHECKS = 1"); //make sure that foreign keys are checked whenever modifying tables
    }

    function designate_primary_keys(&$pdo, $table_name, $keys) {
        /*
            for a table, takes one or more columns and designates them as primary keys in the database
        */
        if (is_array($keys)) {
            $string_key = implode(", ", $keys); //smush all keys together into a single string
        }
        else {
            $string_key = $keys; //if only one key, we just use it
        }
        echo "Adding ($string_key) as primary keys to $table_name<br  />";
        $pdo -> query("ALTER TABLE $table_name ADD PRIMARY KEY($string_key)"); //designate all of the keys listed as primary keys
    }

    function validate_username($username) {
        if (strlen($username) < 4) return "Usernames must be at least 4 characters.<br  /><br  />";
        elseif (preg_match('/[^a-zA-Z0-9_]/', $username) == 1) return "Only a-z, A-Z, 0-9 and _ allowed in Usernames.<br  /><br  />";
        else return "";
    }

    function validate_password($password) {
        if (strlen($password) < 8) return "Passwords must be at least 8 characters<br  /><br  />";
        elseif (preg_match('/[a-z]/', $password) == 0 || preg_match('/[A-Z]/', $password) == 0 || preg_match('/[0-9]/', $password) == 0) {
            return "Passwords require one each of a-z, A-Z and 0-9,<br  /><br  />";
        }
        else return "";
    }

    function add_auto_incrementation_to_primary_keys(&$pdo, $table, $primary_key, $data_type) {
        /*
            Takes a table, given a primary key inside it, and as long as it is a form of an integer, sets it to be an auto_incrementing value
        */
        if (strpos($data_type, "INT")) { //if the primary key is some form of an integer
            echo "Changing $primary_key in $table to be an auto_incrementing value." . "<br  />"; 
            $pdo->query("ALTER TABLE $table MODIFY $primary_key $data_type AUTO_INCREMENT"); //make the primary key an auto-increment column
        }
    }

    function remove_arrays_from_array($array) {
        /*
            strips out all arrays from inside an array
        */
        foreach ($array as $key => $value) {//iterates through an array
            if (is_array($value)) {//if the value at that position of the array is another array
                unset($array[$key]); //drop it from the array
            }
        }
        return $array; //return the array without the inside arrays
    }

    function designate_foreign_keys(&$pdo, $table, $columns, $possible_foreign_keys) {
        /*
            from a list of all possible foreign keys, compare them to all columns in an array, and if the column names match, designate it as a foreign key
        */
        foreach ($possible_foreign_keys as $key_table => $key) {//iterate through tables
            if (!($key_table == $table)){ //if the table that the primary key is in is not the table that we want to check to see if any foreign keys exist in
                if (in_array($key, $columns)) { //check to see if the foreign key is in the table
                    echo "Adding $key from $key_table as a foreign key to $table($key)<br  />"; //if it is, designate it as a foreign key
                    $pdo->query("ALTER TABLE $table ADD FOREIGN KEY($key) REFERENCES $key_table($key)");
                }
            }
        }
    }

    function create_tables_from_keyed_array(&$pdo, $tables, $attribute_data_types) {
        foreach ($tables as $table => $attributes) { //for each of the tables
            $query = ''; //here will go all of the variables and data types
            foreach ($attributes as $attribute) { //for each attribute in the table
                $data_type = $attribute_data_types[$attribute]; //get the data type of the specific attribute
                $query .= "$attribute $data_type, "; //create a big string that contains all the variables and their types seperated by commas (e.g. username VARCHAR(32), pass VARCHAR(255))
            }
            $query = substr($query, 0, -2); //remove the trailing comma
            //echo $query . "<br  />"; //quickly display it
            create_table($pdo, $table, $query); //create the table
        }
    }

    function sanitise_string(&$pdo, $var) {
        /*
            From an input string, sanitise it to protect against mysql injection, and any other problems that may come from user input
        */
        $var = strip_tags($var); //remove any identified html tags in the supplied string
        $var = htmlentities($var); //any special characters are converted to the html special character equivalent, e.g. & -> &amp;
        $result = $pdo->quote($var); //put quotes around the string, and escape any special characters still inside it for some reason
        return str_replace("'", "", $result); //remove all quotes in the string, and return it
    }

    function initialise_mysql_connection($host, $data, $user, $pass) {
        /*
            Initialises a PDO object as a connection to the database in question. Uses try and catch to deal with errors, and returns the pdo object
        */
        $chrs = MYSQL_CHARSET; //set the charset we will use, here utf-8 with 4 bytes per character
        $attr = "mysql:host=$host;dbname=$data;charset=$chrs"; //standard string used to connect to the database, including hostname, database name and charset to use
        $opts = [ //Configures the connection with mysql database
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, //Throw PDO exceptions if an error occurs
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, //Set the default style for returns from the database, here as an array indexed by the columns.
            PDO::ATTR_EMULATE_PREPARES => false, //Tell PDO not to try to 'emulate' prepared statements
        ];

        try { //Creates a PDO object as the connection with the database based on variables
            $pdo = new PDO($attr, $user, $pass, $opts);
        } catch (\PDOException $e) { //If it fails, catch any errors and throw them as such
            throw new \PDOException($e->getmessage(),(int)$e->getCode());
        }
        return $pdo; //return the PDO object for use in mainline code
    }

    function show_profile(&$pdo, $user) {
        /*
            Get all elements of a user's profile, and display them for the user
        */
        if(file_exists("$user.jpg")) { //get the profile picture
            echo "<img src='$user.jpg' style='float:left;'  />";
        }

        $stm = $pdo->prepare("SELECT * FROM profiles WHERE user=?"); //prepare a statement to get the users data, with placeholders for username
        $stm->execute(array($user)); //execute the statement, replacing the placeholders with the username

        while ($row = $stm->fetch()) { //fetch the results of the statement as an array
            die(stripslashes($row['text']) . "<br style='clear:left;  /><br  />"); //attempt to remove any slashes from the text, quitting if it fails
        }

        echo "<p>Nothing to see here, yet</p><br  />";
    }

    function generate_random_string() {
        //generate a random string in a special ** way. A little more random than before
        return substr(md5(rand()), 0, 7);
    }

    function check_user_status() {
        /*
            checks the session variables to see if the user is logged in, returning appropriate values if they are
        */
        if (isset($_SESSION['user'])) { //check the session variables
            $user = $_SESSION['user'];
            $logged_in = TRUE;
            $userstr = ": " . $user;
            $logged_in_as = "You are logged in as $user";
        }
        else {
            $user = '';
            $userstr = "";
            $logged_in = FALSE;
            $logged_in_as = "You are not logged in";
        }
        return array($user, $userstr, $logged_in, $logged_in_as);
    }

    function get_unit_stats(&$pdo, $unit_id) {
        $get_stats = $pdo->query("SELECT * FROM unit AS u JOIN unit_type AS ut ON ut.unit_type=u.unit_type WHERE unit_id = $unit_id");
        while ($result = $get_stats->fetch()) {
            $stats = $result;
        }
        update_shield_hull($stats);
        unset($stats['unit_type']);

        $shops_query = $pdo->query("SELECT s.shop_id, s.shop_name FROM shop AS s JOIN units_in_shop AS us ON us.shop_id=s.shop_id WHERE unit_id = $unit_id");
        $shops = [];
        while ($result = $shops_query->fetch()) {
            $shops[] = $result;
        }

        $complement_query = $pdo->query("SELECT uc.quantity, c.alias, c.is_crew FROM unit_complement AS uc JOIN complement AS c ON uc.complement_id=c.complement_id WHERE unit_id = $unit_id");
        $crew = [];
        $complement = [];
        while ($result = $complement_query->fetch()) {
            if ($result['is_crew'] === 1) {
                $crew[$result['alias']] = $result['quantity']; 
            } else {
                $complement[$result['alias']] = $result['quantity']; 
            }
        }
        $stats['in_shops'] = $shops;
        $stats['complement'] = $complement;
        $stats['crew'] = $crew;
        
        $table = 'unit_armament';
        $get_stats = $pdo->query("SELECT * FROM $table WHERE unit_id = $unit_id");

        $armament = array();
        $armament_id_array = array();
        while ($result = $get_stats->fetch()) {
            unset($result['unit_id']);
            $armament[] = $result;
            if (!in_array($result['armament_id'], $armament_id_array)) {
                $armament_id_array[] = $result['armament_id'];
            }
        }
        $armament_array = [];
        foreach ($armament_id_array as $armament_id) {
            $emplacement = array();
            $armament_query = $pdo->query("SELECT a.ammo, w.weapon_type FROM armament AS a JOIN weapon AS w ON a.weapon_id=w.weapon_id WHERE armament_id=$armament_id");
            while ($result = $armament_query->fetch()) {
                $emplacement[] = array(
                    'ammo' => $result['ammo'], 
                    'weapon_type' => $result['weapon_type']
                );
            }
            $armament_array[$armament_id] = $emplacement;
        }
        foreach ($armament as $key => $emplacement) {
            $armament[$key]['weapon'] = $armament_array[$emplacement['armament_id']];
            unset($armament[$key]['armament_id']);
        }

        $stats['armament'] = $armament;
        return $stats;
    }

    function update_shield_hull(&$stats) {
        if (isset($stats['shield'])) {
            $stats['shield'] = get_integrity_desc($stats['shield']);
        }
        if (isset($stats['hull'])) {
            $stats['hull'] = get_integrity_desc($stats['hull']);
        }
    }

    function verify_unit_id(&$pdo, $unit_id) {
        $query = $pdo->query("SELECT * FROM unit WHERE unit_id = '$unit_id'");
        if (!is_numeric($unit_id)) {
            return "Unit_id must be an integer, please try again.";
        } elseif ($query->rowCount() == 0) {
            return "That unit does not exist, please try again";
        } else return '';
    }

    function get_integrity_desc($class) {
        switch ($class) {
            case 8: return "Very Strong";
            case 7: return "Strong";
            case 6: return "Above Average";
            case 5: return "Average";
            case 4: return "Below Average";
            case 3: return "Weak";
            case 2: return "Very Bad";
            default: return "No";
        }
    }

    /*
    function get_unit_stats(&$pdo, $unit_id) {
        return array(
            'name' => 'Imperial II-Class Star Destroyer',
            'type_description' => 'Capital Ship',
            'alias' => 'ISD II',
            'price' => 125000000,
            'modslots' => 3,
            'uc_limit' => NULL,
            'length' => 1600.0,
            'height' => 454.6,
            'width' => 952.9,
            'hyperdrive' => '2.0',
            'backup' => '8.0',
            'mglt' => 60,
            'kmh' => 975,
            'shield' => 'Above Average',
            'hull' => 'Above Average',
            'sbd' => 4800,
            'ru' => 2272,
            'points' => 7,
            'is_special' => 0,
            'notes' => "The Imperial II-class Star Destroyer, also known as the Imperial II-class Destroyer and colloquially the ImpStar Deuce, was a Star Destroyer model that was derived from the Imperial I-class Star Destroyer.",
            'in_shops' => array(
                array('shop_id' => 1, 'shop_name' => 'Empire of the Hand Shop'), 
                array('shop_id' => 1, 'shop_name' => 'Eriadu Authority Shop'), 
                array('shop_id' => 1, 'shop_name' => 'Greater Maldrood Shop'), 
                array('shop_id' => 1, 'shop_name' => 'Pentastar Alignment Shop'), 
                array('shop_id' => 1, 'shop_name' => 'X1&#039;s Empire Shop'), 
                array('shop_id' => 1, 'shop_name' => 'Zsinj&#039;s Empire Shop')
            ),
            'armament' => array(
                // ammo, type, battery, range, firelink, type, quantity, direction
                array(array(0, 'Heavy Turbolaser'), 2, NULL, 0, ':Batteries', 20, 'Fore'),
                array(array(0, 'Heavy Turbolaser'), 2, NULL, 0, ':Batteries', 15, 'Port'),
                array(array(0, 'Heavy Turbolaser'), 2, NULL, 0, ':Batteries', 15, 'Starboard'),
                array(array(0, 'Heavy Turbolaser'), 1, NULL, 0, ':Cannons', 20, 'Fore'),
                array(array(0, 'Heavy Turbolaser'), 1, NULL, 0, ':Cannons', 10, 'Port'),
                array(array(0, 'Heavy Turbolaser'), 1, NULL, 0, ':Cannons', 10, 'Starboard'),
                array(array(0, 'Heavy Turbolaser'), 1, NULL, 0, ':Cannons', 10, 'Aft'),
                array(array(0, 'Turbolaser'), 2, NULL, 0, ':Batteries', 26, ''),
                array(array(0, 'Heavy ION'), 1, NULL, 0, ':Cannons', 10, 'Fore'),
                array(array(0, 'Heavy ION'), 1, NULL, 0, ':Cannons', 5, 'Port'),
                array(array(0, 'Heavy ION'), 1, NULL, 0, ':Cannons', 5, 'Starboard'),
                array(array(0, 'Heavy Turbolaser', 0, 'Heavy ION'), 8, NULL, 0, 'Octuple Barbette:', 8, ''),
                array(array(0, 'Tractor Beam'), 1, NULL, 0, 'Phylon Q7:Projectors', 6, 'Fore'),
                array(array(0, 'Tractor Beam'), 1, NULL, 0, 'Phylon Q7:Projectors', 2, 'Port'),
                array(array(0, 'Tractor Beam'), 1, NULL, 0, 'Phylon Q7:Projectors', 2, 'Starboard')
            ),
            'complement' => array(
                'starfighter' => 78,
                'shuttle' => 36,
                'large vehicles' => 20,
                'medium vehicles' => 30,
                'modular garrison' => 1,
                'passengers' => 9700,
                'Cargo Capacity' => 36000,
                'Consumables' => 2190
            ),
            'crew' => array(
                'crew' => 36755,
                'gunner' => 330,
                'Minimum Crew' => 5000
            )
        );
    }
    */

    function mysql_stat_names_to_display_names($stat) {
        if (array_key_exists($stat, DISPLAY_NAMES)) {
            $stat = DISPLAY_NAMES[$stat];
        }
        $stat = ucwords($stat);
        return $stat;
    }

    function display_simple_stat($stat, $value) {
        $stat = mysql_stat_names_to_display_names($stat);
        echo "$stat: ";
        print_r($value);
        echo "<br  />";
    }

    function display_unit_stats($unit) {
        /*
            A function that can be called to easily display all stats of a unit
        */

        echo "<table class='unit-table'><tbody>";

        display_unit_name_string($unit);

        display_unit_type_price_string($unit);

        display_uc_limit($unit);

        display_dimensions_string($unit);

        display_speed_string($unit);

        display_integrity_stats($unit);

        display_armament($unit);

        display_complement($unit);

        display_crew($unit);

        display_shops_unit_in($unit);

        display_notes($unit);

        foreach ($unit as $stat => $value) {
            if ($value === NULL) echo "$stat<br  />";
            else display_simple_stat($stat, $value);
        }
        echo "</tbody></table>";
    }

    function display_notes(&$stats) {
        if (not_null($stats['notes'])) {
            $notes = $stats['notes'];
            echo "<tr><th colspan=2 class='centre'>Notes</tr></td>";
            echo "<tr><td colspan=2>$notes</td></tr>";
        }
        unset($stats['notes']);
    }

    function display_array_stat($value, $stat) {
        echo "$stat: ";
        print_r($value);
        echo "<br  />";
    }

    function display_speed_string(&$stats) {
        $str = '';

        $str = display_mglt_kmh_stats($stats);

        $str .= display_hyperdrive_string($stats);

        if ($str!='') {
            echo "<tr><th class='centre' colspan=2>Speed</th></tr>";
            echo $str;
        }
    }

    function display_shops_unit_in(&$stats) {
        $randstr = generate_random_string();
        $C = 'constant';
        if (not_null($stats['in_shops'])) {
            echo "<tr><th class=centre colspan=2>In Shops</th></tr>";
            foreach ($stats['in_shops'] as $shop) {
                $shop_id = $shop['shop_id'];
                echo "<tr><td class=centre colspan=2><a href='{$C('WEBSITE_ROOT')}/shop_page/?r=$randstr&sid=$shop_id' data-transition='slide'>";
                echo $shop['shop_name'] . "</a></td></tr>";
            }
        }
        unset($stats['in_shops']);
    }

    function display_mglt_kmh_stats(&$stats) {
        $str = '';

        if (not_null($stats['kmh']) && not_null($stats['mglt'])) {
            $str .= "<tr><td>";
            $str .= $stats['mglt'] . " MGTL";
            $str .= "</td><td class='right-text'>";
            $str .= $stats['kmh'] . "km/h in atmosphere";
        } elseif (not_null($stats['kmh'])) {
            $str .= "<tr><td colspan=2 class='centre'>";
            $str .= $stats['kmh'] . "km/h in atmosphere";
            $str .= "</td></tr>";
        } elseif (not_null($stats['mglt'])) {
            $str .= "<tr><td colspan=2 class='centre'>";
            $str .= $stats['mgkt'] . " MGLT";
            $str .= "</td></tr>";
        }

        unset($stats['mglt']);
        unset($stats['kmh']);
        return $str;
    }

    function display_armament($armament) {}

    function display_complement(&$stats) {
        $complement = $stats['complement'];
        unset($stats['complement']);
        if ($complement != []) {
            $str = '';
            if (isset($complement['Consumables'])) {
                $quantity = convert_days_to_timestr($complement['Consumables']);
                $str .= "<tr><td class='centre' colspan=2>$quantity Consumables</td></tr>";
                unset($complement['Consumables']);
            } 
            if (isset($complement['Cargo Capacity'])) {
                $quantity = $complement['Cargo Capacity'];
                $quantity = add_commas_to_num($quantity);
                $str .= "<tr><td>Cargo Capacity</td><td class='right-text'>$quantity Metric Tonnes</td></tr>";
                unset($complement['Cargo Capacity']);
            }
            $unit_comp_str = '';
            foreach ($complement as $class => $quantity) {
                $stat = mysql_stat_names_to_display_names($class);
                $stat = pluralise($stat);
                $quantity = add_commas_to_num($quantity);
                $unit_comp_str .= "<li>$quantity $stat</li>";
            }
            echo "<tr><th class='centre' colspan=2>Complement</th></tr>";
            if ($unit_comp_str != '') {
                $unit_comp_str = "<tr><td colspan=2><ul>" . $unit_comp_str . "</ul></td></tr>";
                echo $unit_comp_str;
            }
            echo $str;
        }
    }

    function pluralise($phrase) {
        if (strtolower(substr($phrase, -1)) == 's') {
            return $phrase;
        } elseif (strtolower($phrase) == 'crew') {
            return $phrase;
        } else return $phrase . "s";
    }

    function divide_with_remainder($dividend, $divisor) {
        $quotient = intval($dividend / $divisor);
        $remainder = $dividend % $divisor;
        return array($quotient, $remainder);
    }

    function convert_days_to_timestr($days) {
        list($years, $year_remainder) = divide_with_remainder($days, 365);
        list($months, $month_remainder) = divide_with_remainder($year_remainder, 30);

        $str = '';
        if ($years != 0) {
            $str .= "$years years";
        }
        if ($months != 0) {
            $str .= " $months months";
        }
        if ($month_remainder != 0) {
            $str .= " $month_remainder days";
        }

        return trim($str);
    }

    function display_crew(&$stats) {
        $crew = $stats['crew'];
        unset($stats['crew']);
        if ($crew != []) {
            $str = '';
            if (isset($crew['Minimum Crew'])) {
                $quantity = $crew['Minimum Crew'];
                $quantity = add_commas_to_num($quantity);
                $str .= "<tr><td>Minimum Crew</td><td class='right-text'>$quantity</td></tr>";
                unset($crew['Minimum Crew']);
            }
            $unit_crew_str = '';
            foreach ($crew as $role => $quantity) {
                $stat = mysql_stat_names_to_display_names($role);
                $stat = pluralise($stat);
                $quantity = add_commas_to_num($quantity);
                $unit_crew_str .= "<li>$quantity $stat</li>";
            }
            echo "<tr><th class='centre' colspan=2>Crew</th></tr>";
            if ($unit_crew_str != '') {
                $unit_crew_str = "<tr><td colspan=2><ul>" . $unit_crew_str . "</ul></td></tr>";
                echo $unit_crew_str;
            }
            echo $str;
        }
    }

    function display_uc_limit(&$stats) {
        if (not_null($stats['uc_limit'])) {
            echo "<tr><td colspan=2 class='centre'>";
            echo "Max " . $stats['uc_limit'] . " Per UC";
            echo "</td></tr>";
        }
        unset($stats['uc_limit']);
    }

    function display_unit_name_string(&$stats) {
        echo "<tr><th colspan=2>";
        if (not_null($stats['modslots'])) $modslots = $stats['modslots'];
        else $modslots = '?';
        $name = ucwords($stats['name']);
        echo "\t\t\t\t($modslots) <b>$name</b>";
        unset($stats['name']);
        unset($stats['modslots']);

        if (not_null($stats['alias'])) {
            $alias = $stats['alias'];
            echo " AKA <i>'$alias'</i><br  />";
        }
        unset($stats['alias']);
        echo "</td></tr>";
    }

    function not_null($val) { return (!($val === NULL)); }

    function add_commas_to_num($num_string) {
        $pos = strrpos($num_string, '.');
        if ($pos) {
            return add_commas_to_num(substr($num_string, 0, $pos)) . substr($num_string, $pos);
        }
        if (strlen($num_string) < 4) {
            return $num_string;
        }
        else {
            return add_commas_to_num(substr($num_string, 0, -3)) . "," . substr($num_string, -3);
        }
    }

    function display_price($price) {
        $C = 'constant';
        echo " <img src='{$C('WEBSITE_ROOT')}/data/images/credit_symbol.png' alt='credits' height='18px'  />";
        echo add_commas_to_num($price);
    }

    function display_unit_type_price_string(&$stats) {
        echo "<tr><td>";

        if (not_null($stats['points'])) {
            $points = $stats['points'];
            echo "$points Point ";
        }
        if ($stats['is_special'] === 1) {
            echo "<i>Special</i> ";
        }
        echo $stats['type_description'];
        unset($stats['points']);
        unset($stats['type_description']);
        unset($stats['is_special']);

        if (not_null($stats['price'])) {
            echo "</td><td class='right-text'>";
            display_price($stats['price']);
        }

        unset($stats['price']);
        echo "</td></tr>";
    }

    function display_integrity_stats(&$stats) {
        $str = '';
        $str .= generate_integrity_stat_tr($stats['shield'], $stats['sbd'], "Shielding");
        $str .= generate_integrity_stat_tr($stats['hull'], $stats['ru'], "Hull");
        if ($str != '') {
            echo "<tr><th class='centre' colspan=2>Durability</th></tr>";
            echo $str;
        }
        unset($stats['sbd']);
        unset($stats['shield']);
        unset($stats['hull']);
        unset($stats['ru']);
    }

    function generate_integrity_stat_tr($descriptor, $value, $type) {
        $str = '';
        $units = 'SBD';
        if ($type == 'Hull') $units = 'RU';
        if (not_null($descriptor) && not_null($value)) {
            $str .= "<tr><td>$descriptor $type</td>";
            $str .= "<td class='right-text'>$value $units</td></tr>";
        } elseif (not_null($descriptor)) {
            $str .= "<tr><td colspan=2 class='centre'>$descriptor $type</td></tr>";
        } elseif (not_null($value)) {
            $str .= "<tr><td colspan=2 class='centre'>$value $units</td></tr>";
        }
        return $str;
    }

    function display_dimensions_string(&$stats) {
        /*
            A function to take a array of unit stats, display the dimensions of the ship, and then unset the values so they are not displayed later.
        */
        $dimensions = ['length', 'width', 'height']; //all of the possible dimensions in 3 dimensions
        $dimensions_str = '';
        foreach ($dimensions as $dimension) { //iterate through them all
            if (not_null($stats[$dimension])) { //if the unit has a value for that dimension
                $dimensions_str .= "<tr><td>";
                $dimensions_str .= ucwords($dimension) . "</td>";
                $dimensions_str .= "<td class='right-text'>" . add_commas_to_num($stats[$dimension]) . " meters</td></tr>"; //format and display it properly.
            }
            unset($stats[$dimension]); //remove the dimension from the array, whether or not it exists.
        }
        if ($dimensions_str != '') {
            echo "<tr><th class='centre' colspan=2>Dimensions</th></td>";
            echo $dimensions_str;
        }
    }

    function display_hyperdrive_string(&$stats) {
        $hyperdrive_str = '';
        if (not_null($stats['hyperdrive'])) {
            $hyperdrive_str = "<tr><td>Hyperdrive: Class " . $stats['hyperdrive'] . "</td>";
        }
        if (not_null($stats['backup'])) {
            $hyperdrive_str .= "<td class='right-text'>Backup: Class " . $stats['backup'] . "</td>";
        }
        unset($stats['hyperdrive']);
        unset($stats['backup']);
        if ($hyperdrive_str !== '') {
            return $hyperdrive_str . "</tr>";
        }
    }

    function ingest_shop_to_array($shop, $unit_id_key) {
        $shop = explode("\n", $shop); //seperate the lines into seperate values in the variables
        $units = array(); //
        $type = ''; //initialise type to avoid errors later
        foreach ($shop as $index => $line) { //for each line in the shop
            $line = trim($line);
            if ($line == "") { //if it is empty, unset the line and continue with the next element
                unset($shop[$index]);
            }
            elseif (strpos($line, "*") === 0) { //test to see if the first character is an asterix, denoting the shop name
                $shop_name = trim($line, "*");
            }
            elseif (strpos($line, "_") === 0) {//test to see if there are any _, denoting a unit type in the shop
                $type = trim($line, "_");
                //if (!(in_array($type, $types))) $types[] = $type; //Old check to gather names of all unit types from shops
            }
            elseif (is_numeric(substr($line, 1, 1)) || (strrpos($line, "-") >= 3 && substr($line, 0, 1) != "[")) { 
                /*test to see if the 2nd character in a string is numeric, or the first character is an m-dash, or if there is an m-dash past index 3 AND the line doesn't start with a square bracket
                Will accept:
                (1) YM-2800 Limpet - 250,000
                OR
                Wyyyschokk - 7,500,000 (Max 10 Per UC)
                But not:
                [Adds one of the following: A blaster cannon, 6 round grenade launcher or stun blaster.]
                OR
                - (1) Armaments Modification - 10,000
                */
                $unit = convert_shop_unit_string_to_unit($line, $type);
                $units[] = $unit; //add the new unit to the array of units
            }
            elseif (strpos($line, "-") === 0) { //deals with the case where the unit is a mod
                $unit = convert_shop_unit_string_to_unit($line, ($type . " Mod")); //just adds the mod classification to the unit, so it can be checked later
                $units[] = $unit;
            }
            else { //otherwise, assume is is a note for the last processed unit.
                $index = count($units) - 1;
                $units[$index]['notes'] .= trim($line, " \t\n\r\0\x0B-()[]{}:;");
            }
        }
        return array($shop_name, $units); //return the shop object
    }

    function convert_shop_unit_string_to_unit($shop_str, $type) {
        /*
            Take a string of information like the one below, and convert it into a unit array with as much information as possible.
            (0) Mediator-Class Battlecruiser - 270,000,000 (Max 3 Per UC)
        */
        $shop_str = trim($shop_str, " \t\n\r\0\x0B-"); //trim the string down, including removing first '-' if the unit is a mod and indented with one.
        $modslots = get_modslots_from_shop_str($shop_str); //take any number that may be in the first brackets at the start of a string, as that will most likely be modslots
        $remaining_line = trim_modslots_from_str($shop_str); //remove that number and bracket pair, triming whitespace
        $name = get_name_from_shop_str($remaining_line); //Remove the name from the shop string
        $remaining_line = remove_name_from_shop_str($remaining_line); //keep the rest

        $str_array = explode_string_at_any_open_bracket($remaining_line); //split the string into multiple parts, one including price, and others including notes and possible uc_limit

        //assume the first in the array contains the price
        $remaining_line = $str_array[0]; //get the first part
        $price = get_number_from_comma_string($remaining_line); //get the number from it
        unset($str_array[0]); //remove the first part from the array

        $notes = ''; //initialise notes so there is no errors
        foreach ($str_array as $line) { //iterate through the remaining line
            if ((!(strpos($line, "UC")===False)) && (!(strpos($remaining_line, "Max")===False))) { //if Max and UC exist in the string, as in (Max 72 Per UC)
                $uc_limit = get_uc_limit_from_str($line); //extract the uc_limit from the string
            }
            else {
                $notes .= " " . trim($remaining_line, " \t\n\r\0\x0B-()[]{}:;"); //trim the rest and add it to the noted for the unit
            }
        }
        $notes = trim($notes); // remove whitespace from the notes string to save space
        if (!(isset($uc_limit))) { //if the unit has no uc_limit, set it to the appropriate value
            $uc_limit = 'NULL';
        }
        $unit = array( //format the unit
            'modslots' => $modslots,
            'unit_name' => $name,
            'type' => $type,
            'price' => $price,
            'notes' => $notes,
            'uc_limit' => $uc_limit
        );

        return $unit; //return the unit
    }

    function get_uc_limit_from_str($str) {
        // For string in format (Max 72 Per UC), will strip all other parts and return the number in the centre
        $str = trim($str, " MaxPerUC()"); //remove all characters that can exist in the string except for numbers from either end
        $uc_limit = intval($str); //make the string an integer
        return $uc_limit; //return the integer
    }

    function remove_comma_from_str($str) {
        // Simply remove commas from a string
        return str_replace(',', '', $str);
    }

    function get_number_from_comma_string($str) {
        /*
            Take a string of numbers with commas seperating digits in groups of 3s, and take the value of that number
        */
        $str = remove_comma_from_str($str); //remove all commas from the string
        return intval($str); //turn the string into an integer value until it reaches a non-numeric character
    }

    function explode_string_at_any_open_bracket($str) {
        /*
            Go through a string, and if it encounters any open brackets, explode them into different values
        */
        $str = str_replace(array('{', '['), '(', $str); //replace all { and [ with (
        $str_array = explode('(', $str); //explode the string at (
        foreach ($str_array as $index => $str) {
            $str_array[$index] = trim($str, " \t\n\r\0\x0B-()[]{}"); //trim all excess from each string, including the closing brackets of the string
        }
        return $str_array;
    }

    function get_name_from_shop_str($str) {
        /*
            Assuming that modslots are already removed from a string, extract the name of a unit from a shop string
        */
        $last_m_dash_position = strrpos($str, '-'); //find the last m dash, assuming that it indicates the location of a m-dash splitting the name of the unit and it's price
        $name = trim(substr($str, 0, $last_m_dash_position)); //grab everything up to the m dash, and trim whitespace
        return $name;
    } 

    function remove_name_from_shop_str($str) {
        /*
            take everything after the name of the string, triming whitespace and m-dashes
        */
        $last_m_dash_position = strrpos($str, '-'); //find the last m dash, assuming that it indicates the location of a m-dash splitting the name of the unit and it's price
        $str = trim(substr($str, $last_m_dash_position), " \t\n\r\0\x0B-"); //take everything from the last m-dash, removing it and whitespace.
        return $str;
    }

    function get_modslots_from_shop_str($str) {
        /*
            Takes a bracketted modslot pair from the start of the string, and returns the number inside it, as well as the string without the modslots number.
            Should deal with 2 digit modslot values now, like with the Loronar.
        */
        $str = trim($str);

        if (substr($str, 0, 1) == '(') {
            $mod_str = substr($str, 1);
            $modslots = intval($mod_str);
        }
        return $modslots;
    }

    function trim_modslots_from_str($str) {
        /*
            Takes a string, and removes a bracket pair from the start of the string, and everything it contains
        */
        if (substr($str, 0, 1) == '(') { //if there is a bracket pair at the start of the string
            $str = trim(substr($str, strpos($str, ')')+1)); //take only everything after the first closing bracket
        }
        return $str; //return the string without the first bracket pair
    }

    function find_id_for_unit($unit, $unit_id_key) {
        $name = $unit['unit_name'];
        if (array_key_exists($name, $unit_id_key)) {
            $id = $unit_id_key[$name];
        }
        else {
            $id = NULL;
        }
        return $id;
    }