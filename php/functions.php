<?php
    define("WEBSITE_ROOT", "http://localhost/the_holonet_archives/public_html");
    //define("WEBSITE_ROOT", "https://beta.myria.dev");
    define("DOCUMENT_ROOT", "/var/www/html/the_holonet_archives/"); //Linux document root
    //define ("DOCUMENT_ROOT", "/var/www/beta.myria.dev/");
    //define("DOCUMENT_ROOT", 'C:\xampp\htdocs\the_holonet_archives\\'); // Windows Document root for Xampp install
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
    //define("FILENAME_SLASH", '\\'); //Stupid windows backslash because they are special and want to be all backwards compatible but it makes it harder for everyone developing on any of their platforms

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
        $get_stats = $pdo->query("SELECT * FROM unit AS u LEFT JOIN unit_type AS ut ON ut.unit_type=u.unit_type WHERE unit_id = '$unit_id'");
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
        
        $skill_query = $pdo->query("SELECT s.skill, us.value FROM unit_skill AS us JOIN skill AS s ON us.skill_id=s.skill_id WHERE us.unit_id=$unit_id");
        $skills = [];
        while ($row = $skill_query->fetch()) {
            $skills[$row['skill']] = $row['value'];
        }
        $stats['skills'] = $skills;

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

    function get_shop_stats(&$pdo, $shop_id) {
        $result = $pdo->query(<<<_END
        SELECT
            u.unit_id,
            u.name,
            ut.type_description,
            u.modslots,
            s.shop_name,
            u.price,
            u.notes,
            u.uc_limit
        FROM
            units_in_shop AS us
        JOIN shop AS s
        ON
            s.shop_id = us.shop_id
        JOIN unit AS u
        ON
            us.unit_id = u.unit_id
        LEFT JOIN unit_type AS ut
        ON
            ut.unit_type = u.unit_type
        WHERE
            s.shop_id = $shop_id
        ORDER BY
            u.unit_type
        _END);

        $units = [];
        while ($row = $result->fetch()) {
            $units[] = $row;
        }

        $shop_name = $units[0]['shop_name'];
        foreach ($units as $index => $unit) {
            unset($units[$index]['shop_name']);
        }

        return array($shop_name, $units);
    }

    function collect_unit_types($units) {
        $unit_types = [];
        foreach ($units as $unit) {
            if (!in_array($unit['type_description'], $unit_types)) {
                $unit_types[] = $unit['type_description'];
            }
        }
        return $unit_types;
    }

    function swap_array_values(&$array, $a, $b) {
        $temp = $array[$b];
        $array[$b] = $array[$a];
        $array[$a] = $temp;
    }
    
    function sort_units_by_attribute(&$units, $attr) {
        /*
            simple binary insertion sort to sort units by any attribute provided.
        */
        $arr_size = sizeof($units);
        for ($i = 1; $i < $arr_size; $i++) {
            $lower = 0;
            $upper = $i-1;
            while ($lower <= $upper) {
                $middle = intval(($lower + $upper)/2);
                if ($units[$i][$attr] >= $units[$middle][$attr]) $lower = $middle + 1;
                else $upper = $middle - 1;
            }
            if ($units[$i][$attr] >= $units[$middle][$attr]) $position = $middle + 1; 
            else $position = $middle;
            for ($j = $i; $j > $position; $j--) {
                swap_array_values($units, $j, $j-1);
            }
        }

        return $units;
    }

    function search_for_units($units, $attr, $value) {
        /*
            Quick search for a unit who has an attribute equal to the value given.
        */
        $found_at = FALSE;
        foreach ($units as $index => $unit) {
            if ($unit[$attr] == $value) {
                $found_at = $index;
                break;
            }
        }
        return $found_at;
    }

    function get_units_of_attr($units, $attr, $value) {
        $found_at = search_for_units($units, $attr, $value);
        $units_of_attr = [];
        while ($found_at !== FALSE) {
            $units_of_attr[] = $units[$found_at];
            unset($units[$found_at]);
            $found_at = search_for_units($units, $attr, $value);
        }
        return $units_of_attr;
    }

    function fetch_all_shops(&$pdo) {
        $all_shops = [];
        $result = $pdo->query("SELECT * FROM shop");
        while ($row = $result->fetch()) {
            $all_shops[] = $row;
        }
        return $all_shops;
    }

    function display_shop_link($shop) {
        $C = 'constant';
        $str = "<a data-transition='slide' class='ui-btn ui-corner-all' href='{$C('WEBSITE_ROOT')}/shop_page?sid=" . $shop['shop_id'] . "'>";
        $str .= $shop['shop_name'] . "</a>";
        return $str;
    }

    function display_shop_unit($unit) {
        $randstr = generate_random_string();
        $C = 'constant';
        $unit_id = $unit['unit_id'];
        $str = "<li><a class='unit-links' data-transition='slide' href='{$C('WEBSITE_ROOT')}/unit_page?uid=$unit_id'>(" . $unit['modslots'] . ") ";
        $str .= $unit['name'];
        $price = $unit['price'];
        if ($price != 0) $str .= " - <img src='{$C('WEBSITE_ROOT')}/data/images/credit_symbol.png' alt='credits' height='18px'  />" . number_format($price);
        if (not_null($unit['uc_limit']) && $unit['uc_limit'] !== 'NULL') $str .= " (Max " . $unit['uc_limit'] . " Per UC)";
        $str .= "</a>";
        if (not_null($unit['notes']) && $unit['notes'] !== '') $str .= "<br  />[" . $unit['notes'] . "]";
        $str .=  "</li>";
        return $str;
    }

    function get_type_value($type) {
        $value = 0;
        switch ($type) {
            case 'Starfighter':
                $value += 1;
            case 'Small Ship':
                $value += 1;
            case 'Capital Ship':
                $value += 1;
            case 'Infantry':
                $value += 1;
            case 'Vehicle':
                $value += 1;
            case 'Space Station':
                $value += 1;
            case 'Modular Garrison':
                $value += 1;
            case 'Car':
                $value += 1;
            case 'Civilian':
                $value += 1;
            case 'Military Installation':
                $value += 1;
            case 'Building':
                $value += 1;
        }
        return $value;
    }

    function sort_unit_types(&$types) {
        /*
            Sorts the unit types based on a 
        */
        $arr_size = sizeof($types);
        for ($i = 1; $i < $arr_size; $i++) {
            $lower = 0;
            $upper = $i-1;
            while ($lower <= $upper) {
                $middle = intval(($lower + $upper)/2);
                if (get_type_value($types[$i]) <= get_type_value($types[$middle])) $lower = $middle + 1;
                else $upper = $middle - 1;
            }
            if (get_type_value($types[$i]) <= get_type_value($types[$middle])) $position = $middle + 1; 
            else $position = $middle;
            for ($j = $i; $j > $position; $j--) {
                swap_array_values($types, $j, $j-1);
            }
        }
    }

    function normalise_unit_types($type) {
        /*
            Normalise the provided type of a unit, returning a more general term for displaying in shops and the like.
        */
        switch ($type) {
            case 'Small Vehicle':
                return 'Vehicle';
            case 'Medium Vehicle':
                return 'Vehicle';
            case 'Large Vehicle':
                return 'Vehicle';
            case 'Juggernaut':
                return 'Vehicle';
            case 'Shuttle':
                return 'Small Ship';
            case 'Transport':
                return 'Small Ship';
            case 'Heavy Transport':
                return 'Small Ship';
            case 'Gunship':
                return 'Vehicle';
            case 'Airspeeder':
                return 'Vehicle';
            default:
                return $type;
        }
    }

    function display_shop($shop_name, $units) {
        foreach ($units as $index => $unit) {
            $units[$index]['type_description'] = normalise_unit_types($unit['type_description']); //get rid of subclasses like small_vehicles and medium_vehicles and shuttles
        }
        $types = collect_unit_types($units);
        sort_unit_types($types); //sort the types by their assigned values in get_type_value so they are displayed in order

        echo "<h1 class='centre'>$shop_name</h1>";

        foreach ($types as $type) {
            
            echo "<div><h3>" . pluralise($type) . "</h3><ul>";
            $units_of_type = get_units_of_attr($units, 'type_description',  $type);

            sort_units_by_attribute($units_of_type, 'price');

            foreach ($units_of_type as $unit) {
                echo display_shop_unit($unit);
            }
            echo "</ul></div>";
        }
    }

    function validate_id(&$pdo, $id, $table) {
        $query = $pdo->query("SELECT * FROM $table WHERE $table" . "_id = '$id'");
        if (!is_numeric($id)) {
            return "ID must be an integer, please try again.";
        } elseif ($query->rowCount() == 0) {
            return "That entry does not exist, please try again";
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
            ),
            'skills' => array(
                'Marksmanship' => 4,
                'CQC' => 3
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

    function display_skills(&$stats) {
        $skills = $stats['skills'];
        unset($stats['skills']);
        if ($skills != []) {
            $str = '';
            foreach ($skills as $skill => $value) {
                $stat = mysql_stat_names_to_display_names($skill);
                $str .= "<tr><td>$stat</td><td class='right-text'>$value</tr>";
            }
            echo "<tr><th class='centre' colspan=2>Skills</th></tr>";
            if ($str != '') echo $str;
        }
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

        display_max_height($unit);

        display_integrity_stats($unit);

        display_armament($unit);

        display_complement($unit);

        display_crew($unit);

        display_skills($unit);

        display_shops_unit_in($unit);

        display_notes($unit);

        display_wiki_link($unit);

        foreach ($unit as $stat => $value) {
            if ($value === NULL) echo "$stat<br  />";
            else display_simple_stat($stat, $value);
        }
        echo "</tbody></table>";
    }

    function display_wiki_link(&$stats) {
        if (not_null($stats['wiki_link'])) {
            $wiki_link = $stats['wiki_link'];
            echo "<tr><th colspan=2 class='centre'>Wookieepedia Article</th></tr>";
            echo "<tr><td colspan=2 class='centre'><a href='$wiki_link' target='_blank' rel='external'>$wiki_link</a></td></tr>";
        }
        unset($stats['wiki_link']);
    }

    function display_notes(&$stats) {
        if (not_null($stats['notes'])) {
            $notes = $stats['notes'];
            echo "<tr><th colspan=2 class='centre'>Notes</tr></td>";
            echo "<tr><td colspan=2>$notes</td></tr>";
        }
        unset($stats['notes']);
    }

    function display_max_height(&$stats) {
        if (not_null($stats['max_height'])) {
            echo "<tr><td colspan='2' class='centre'>";
            echo "Maximum Altitude: " . $stats['max_height'];
            echo "</td></tr>";
        }

        unset($stats['max_height']);
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
            $str .= $stats['mglt'] . " MGLT";
            $str .= "</td></tr>";
        }

        unset($stats['mglt']);
        unset($stats['kmh']);
        return $str;
    }

    function consolidate_armament($armament) {
        $new_armament = [];
        foreach ($armament as $i => $weapon) {
            $is_found = FALSE;
            foreach ($new_armament as $j => $new_weapon) {
                if (is_same_weapon_emplacement($weapon, $new_weapon)) {
                    $new_armament[$j]['locations'][] = array(
                        'quantity' => $weapon['quantity'],
                        'direction' => $weapon['direction']
                    );
                    $is_found = TRUE;
                    break;
                }
            }
            if (!$is_found) {
                $armament[$i]['locations'] = array();
                $armament[$i]['locations'][] = array(
                    'quantity' => $weapon['quantity'],
                    'direction' => $weapon['direction']
                );
                unset($armament[$i]['quantity']);
                unset($armament[$i]['direction']);
                $new_armament[] = $armament[$i];
            }
        }
        return $new_armament;
    }

    function consolidate_quantity($emplacement) {
        $quantity = 0;
        foreach ($emplacement['locations'] as $location) {
            $quantity += $location['quantity'];
        }
        return $quantity;
    }

    function get_size_word($size) {
        switch ($size) {
            case 2:
                return 'Dual';
            case 3:
                return 'Triple';
            case 4:
                return 'Quad';
            case 5:
                return 'Quintuple';
            case 6:
                return 'Sextuple';
            case 8:
                return 'Octuple';
            default:
                return '';
        }
    }

    function generate_emplacement_list($locations) {
        $str = '';
        $str .= "<ul>";
        foreach ($locations as $location) {
            $str .= "<li>" . trim($location['quantity'] . " " . $location['direction']) . "</li>";
        }
        $str .= "</ul>";
        return $str;
    }

    function format_firelink($firelink) {
        if ($firelink > 0) {
            return " (Firelinked in groups of $firelink)";
        } else return '';
    }

    function format_weapon_range($range) {
        if ($range > 0) {
            return " (range: $range km)";
        } else return '';
    }

    function format_weapon_type($types) {
        $str = '';
        foreach ($types as $type) {
            $str .= " " . $type['weapon_type'];
            if ($type['ammo'] > 0) {
                $str .= " (Ammo: " . $type['ammo'] . ")";
            }
            $str .= " or";
        }
        $str = substr($str, 0, -3); //remove the last or and space
        return $str;
    }

    function format_weapon($weapon_type, $weapons) {
        $weapons = format_weapon_type($weapons);
        return str_replace(':', " $weapons ", $weapon_type);
    }

    function display_emplacement($emplacement) {
        $str = '<li>';
        $quantity = consolidate_quantity($emplacement);
        $str .= $quantity . " ";
        $size_word = get_size_word($emplacement['battery_size']);
        $str .= $size_word . " ";
        unset($emplacement['battery_size']);

        $emplacement_list_str = '';
        if (sizeof($emplacement['locations']) >= 2) {
            $emplacement_list_str = generate_emplacement_list($emplacement['locations']);
        } else {
            $str .= $emplacement['locations'][0]['direction'];
        }
        unset($emplacement['locations']);

        $str .= " " . format_weapon($emplacement['weapon_type'], $emplacement['weapon']);

        $str .= format_firelink($emplacement['firelink']);
        unset($emplacement['firelink']);

        $str .= format_weapon_range($emplacement['weapon_range']);
        unset($emplacement['weapon_range']);

        $str .= $emplacement_list_str;
        $str .= '</li>';
        return $str;
    }

    function is_same_weapon_emplacement($emplacement_1, $emplacement_2) {
        /*
            Takes two weapon emplacements, and compares everything except quantity and direction, returning true if they are the same, and false if they are not.
        */
        unset($emplacement_1['quantity']);
        unset($emplacement_1['direction']);
        unset($emplacement_1['locations']);
        unset($emplacement_2['quantity']);
        unset($emplacement_2['direction']);
        unset($emplacement_2['locations']);
        return $emplacement_1 === $emplacement_2;
    }

    function display_armament(&$stats) {
        /*
            Take the armament of a unit and display it as a organised unordered list. Unset it so that it isn't displayed twice
        */
        $armament = $stats['armament'];
        $armament = consolidate_armament($armament);
        $str = '';
        if ($armament !== []) {
            $str .= "<tr><th colspan=2 class='centre'>Armament</th></tr>";
            $str .= "<tr><td colspan=2><ul>";
            foreach ($armament as $emplacement) {
                $str .= display_emplacement($emplacement);
            }
            $str .= "</ul></td></tr>";
        }
        echo $str;
        unset($stats['armament']);
    }

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
                $quantity = number_format($quantity);
                $str .= "<tr><td>Cargo Capacity</td><td class='right-text'>$quantity Metric Tonnes</td></tr>";
                unset($complement['Cargo Capacity']);
            }
            $unit_comp_str = '';
            foreach ($complement as $class => $quantity) {
                $stat = mysql_stat_names_to_display_names($class);
                $stat = pluralise($stat);
                $quantity = number_format($quantity);
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

    function depluralise($phrase) {
        if (strtolower(substr($phrase, -1)) == 's') {
            return substr($phrase, 0, -1);
        } elseif (strtolower($phrase) == 'crew') {
            return $phrase;
        } elseif (strtolower($phrase) == 'batteries') {
            return 'battery';
        } elseif (strtolower($phrase) == 'infantry') {
            return $phrase;
        } else return $phrase;
    }

    function pluralise($phrase) {
        if (strtolower(substr($phrase, -1)) == 's') {
            return $phrase;
        } elseif (strtolower($phrase) == 'crew') {
            return $phrase;
        } elseif (strtolower($phrase) == 'infantry') {
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
            $str .= "$years Years";
        }
        if ($months != 0) {
            $str .= " $months Months";
        }
        if ($month_remainder != 0) {
            $str .= " $month_remainder Days";
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
                $quantity = number_format($quantity);
                $str .= "<tr><td>Minimum Crew</td><td class='right-text'>$quantity</td></tr>";
                unset($crew['Minimum Crew']);
            }
            $unit_crew_str = '';
            foreach ($crew as $role => $quantity) {
                $stat = mysql_stat_names_to_display_names($role);
                $stat = pluralise($stat);
                $quantity = number_format($quantity);
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
            return number_format(substr($num_string, 0, $pos)) . substr($num_string, $pos);
        }
        if (strlen($num_string) < 4) {
            return $num_string;
        }
        else {
            return number_format(substr($num_string, 0, -3)) . "," . substr($num_string, -3);
        }
    }

    function display_price($price) {
        $C = 'constant';
        echo " <img src='{$C('WEBSITE_ROOT')}/data/images/credit_symbol.png' alt='credits' height='18px'  />";
        echo number_format($price);
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
        if (not_null($stats['hyperdrive']) && not_null($stats['backup'])) {
            $hyperdrive_str = "<tr><td>Hyperdrive: Class " . number_format($stats['hyperdrive'], 1) . "</td>";
            $hyperdrive_str .= "<td class='right-text'>Backup: Class " . number_format($stats['backup'], 1) . "</td>";
        } elseif (not_null($stats['hyperdrive'])) {
            $hyperdrive_str = "<tr><td colspan='2' class='centre'>Hyperdrive: Class " . number_format($stats['hyperdrive'], 1) . "</td>";
        } elseif (not_null($stats['backup'])) {
            $hyperdrive_str .= "<tr><td colspan='2' class='centre'>Backup: Class " . number_format($stats['backup'], 1) . "</td>";
        }
        unset($stats['hyperdrive']);
        unset($stats['backup']);
        if ($hyperdrive_str !== '') {
            return $hyperdrive_str . "</tr>";
        }
    }

    function generate_type_list(&$pdo, $normalise=FALSE, $pluralise=FALSE) {
        $type_query = $pdo->query("SELECT type_description, unit_type FROM unit_type");
        $types = [];

        while ($row = $type_query->fetch()) {
            $types[$row['unit_type']] = $row['type_description'];
        }

        if ($normalise) {
            foreach ($types as $key => $type) {
                $types[$key] = normalise_unit_types($type);
            }
        }
        if ($pluralise) {
            foreach ($types as $key => $type) {
                $types[$key] = pluralise($type);
            }
        }

        return $types;
    }

    function ingest_shop_to_array(&$pdo, $shop, $get_unit_id=FALSE) {
        $shop = str_replace('\n', "\n", $shop);
        $shop = str_replace('\r', "\r", $shop);
        $shop = explode("\n", $shop); //seperate the lines into seperate values in the variables
        $units = array(); //

        $types = generate_type_list($pdo, TRUE);

        $type = ''; //initialise type to avoid errors later
        foreach ($shop as $index => $line) { //for each line in the shop
            $line = trim($line);
            if ($line == "") { //if it is empty, unset the line and continue with the next element
                unset($shop[$index]);
            }
            elseif ($index == 0) { //test to see if it is the first line, denoting the shop name
                $shop_name = ucwords(strtolower(trim($line, "*")));
            }
            elseif (in_array(depluralise(trim($line, "_")), $types)) {//test to see if the contents of the line are one of the types of units, normalised as per normal shops
                $type = depluralise(trim($line, "_"));
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

        if ($get_unit_id) {
            $unit_id_array = fetch_unit_id_array($pdo);

            foreach ($units as $key => $unit) {
                if (array_key_exists($unit['name'], $unit_id_array)) {
                    $units[$key]['unit_id'] = $unit_id_array[$unit['name']];
                } else {
                    $units[$key]['unit_id'] = NULL;
                }
            }
        }
        return array($shop_name, $units); //return the shop object
    }

    function fetch_unit_id_array(&$pdo) {
        $unit_id_array = [];
        $unit_id_query = $pdo->query("SELECT alias, name, unit_id FROM unit");

        while ($row = $unit_id_query->fetch()) {
            $unit_id_array[$row['name']] = $row['unit_id'];
            if (not_null($row['alias'])) {
                $unit_id_array[$row['alias']] = $row['unit_id'];
            }
        }

        return $unit_id_array;
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
            if ((!(strpos($line, "UC")===False)) && (!(strpos($line, "Max")===False))) { //if Max and UC exist in the string, as in (Max 72 Per UC)
                $uc_limit = get_uc_limit_from_str($line); //extract the uc_limit from the string
            } else {
                $notes .= " " . trim($line, " \t\n\r\0\x0B-()[]{}:;"); //trim the rest and add it to the noted for the unit
            }
        }
        $notes = trim($notes); // remove whitespace from the notes string to save space
        if (!(isset($uc_limit))) { //if the unit has no uc_limit, set it to the appropriate value
            $uc_limit = 'NULL';
        }
        $unit = array( //format the unit
            'modslots' => $modslots,
            'name' => $name,
            'type_description' => $type,
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
        $name = ucwords($name);
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
            Takes a bracketted modslot pair from the start of the string, and returns the number inside it.
            Should deal with 2 digit modslot values now, like with the Loronar.
        */
        $str = trim($str);
        $modslots = 0;

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

    function find_unit_id(&$pdo, $name) {
        $id = FALSE;
        $result = $pdo->query("SELECT unit_id FROM unit WHERE name LIKE '%$name%'");
        if ($result->rowCount() == 1) {
            $id = $result->fetch()['unit_id'];
        } elseif ($result->rowCount() > 1) {
            $id = [];
            while ($row = $result->fetch()) {
                $id[] = $row['unit_id'];
            }
        }
        return $id;
    }

    function check_user_privilege(&$pdo, $username) {
        $privilege_query = $pdo->query("SELECT * FROM user_privilege WHERE username='$username'");

        $privilege = [];

        while ($row = $privilege_query->fetch()) {
            $privilege[] = $row['privilege_id'];
        }

        return $privilege;
    }

    function fetch_unit_type_array(&$pdo) {
        $unit_type_array = [];
        $unit_type_query = $pdo->query("SELECT * FROM unit_type");

        while ($row = $unit_type_query->fetch()) {
            $unit_type_array[$row['type_description']] = $row['unit_type'];
        }

        return $unit_type_array;
    }

    function get_unit_type_number(&$pdo, $type) {
        $types = array_flip(generate_type_list($pdo));

        if (array_key_exists($type, $types)) {
            $type_num = "'" . $types[$type] . "'";
        } else {
            $type_num = "NULL";
        }

        return $type_num;
    }

    function insert_shop_into_database(&$pdo, $shop_name, $units, $is_special=0) {
        foreach ($units as $index => $unit) {
            if ($unit['unit_id'] == '') {
                $type = get_unit_type_number($pdo, $unit['type_description']);
                $notes = $unit['notes'] == '' ? 'NULL' : "'" . $unit['notes'] . "'";
                $pdo->query("INSERT INTO unit(name, modslots, unit_type, price, uc_limit, notes, is_special) VALUES" . "('" . $unit['name'] . "', '" . $unit['modslots'] . "', " . $type . ", '" . $unit['price'] . "', " . $unit['uc_limit'] . ", " . $notes . ", '" . $is_special . "')");

                $unit_id_array = fetch_unit_id_array($pdo);
                $units[$index]['unit_id'] = $unit_id_array[$unit['name']];
            }
        }

        $shop_query = $pdo->query("SELECT * FROM shop WHERE shop_name='$shop_name'");

        if ($shop_query->rowCount() == 0) {
            $pdo->query("INSERT INTO shop(shop_name) VALUES ('$shop_name')");
        }

        $shop_id_query = $pdo->query("SELECT * FROM shop WHERE shop_name='$shop_name'");

        $shop_id = 0;

        while ($row = $shop_id_query->fetch()) {
            $shop_id = $row['shop_id'];
        }

        if ($shop_id >= 0) {
            foreach ($units as $unit) {
                $check_query = $pdo->query("SELECT * FROM units_in_shop WHERE unit_id='" . $unit['unit_id'] . "' AND shop_id=$shop_id");
                if ($check_query->rowCount() === 0) {
                    $pdo->query("INSERT INTO units_in_shop VALUES ('$shop_id', '" . $unit['unit_id'] . "')");
                }
            }
        }
    }

    function extract_stats_from_line($line, &$stats) {
    }

    function extract_speeds(&$stats, $line) {
        $line = str_replace("/", '', $line);
        
        $mglt_position = stripos($line, 'mglt');
        $kmh_position = stripos($line, 'kmh');

        if ($kmh_position === FALSE) {
            $line = trim($line, " :\t\n\r\0\x0Ba..zA..Z");
            $stats['mglt'] = intval($line);
        } elseif ($mglt_position === FALSE) {
            $line = trim($line, " :\t\n\r\0\x0Ba..zA..Z");
            $line = remove_comma_from_str($line);
            $stats['kmh'] = intval($line);
        } else {
            if ($mglt_position > $kmh_position) {
                $mglt_str = substr($line, $kmh_position);
                $kmh_str = substr($line, 0, $kmh_position);
            } else {
                $kmh_str = substr($line, $mglt_position);
                $mglt_str = substr($line, 0, $mglt_position);
            }

            $stats['mglt'] = intval(remove_comma_from_str(trim($mglt_str, " :\t\n\r\0\x0Ba..zA..Z")));
            $stats['kmh'] = intval(remove_comma_from_str(trim($kmh_str, " :\t\n\r\0\x0Ba..zA..Z")));
        }
    }

    function extract_hyperdrive(&$stats, $line) {
        if (str_contains($line, ',')) {
            $lines = explode(',', $line);
        } elseif (str_contains($line, '|')) {
            $lines = explode('|', $line);
        } elseif (str_contains($line, "\n")) {
            $lines = explode("\n", $line);
        } else {
            $lines = array($line);
        }

        foreach ($lines as $line) {
            $backup_pos = stripos($line, 'backup');
            $value = floatval(remove_comma_from_str(trim($line, " :\t\n\r\0\x0Ba..zA..Z")));

            if ($backup_pos !== FALSE) {
                $stats['backup'] = $value;
            } elseif ($value != 0) {
                $stats['hyperdrive'] = $value;
            } 
        }
    }

    function get_strength_no_in_str($str) {
        if (stripos($str, 'above average') !== false) {
            return 6;
        } elseif (stripos($str, 'below average') !== false) {
            return 4;
        } elseif (stripos($str, 'very strong') !== false) {
            return 8;
        } elseif (stripos($str, 'very bad') !== false) {
            return 2;
        } elseif (stripos($str, 'weak') !== false) {
            return 3;
        } elseif (stripos($str, 'average') !== false) {
            return 5;
        } elseif (stripos($str, 'strong') !== false) {
            return 7;
        } elseif (stripos($str, 'no') !== false) {
            return 1;
        } else {
            return NULL;
        }
    }

    function extract_durability(&$stats, $line) {
        if (str_contains($line, '/')) {
            $strength = get_strength_no_in_str($line);
            $strength === NULL ? : $stats['hull'] = $strength;
            $strength === NULL ? : $stats['shield'] = $strength;
            return;
        } elseif (str_contains($line, ',')) {
            $lines = explode(',', $line);
        } else {
            $lines = array($line);
        }

        foreach ($lines as $line) {
            $shield_pos = stripos($line, 'shield');
            $hull_pos = stripos($line, 'hull');

            if ($shield_pos !== FALSE) {
                $strength = get_strength_no_in_str($line);
                $strength === NULL ? : $stats['shield'] = $strength;
            } elseif ($hull_pos !== FALSE) {
                $strength = get_strength_no_in_str($line);
                $strength === NULL ? : $stats['hull'] = $strength;
            }
        }
    }

    function lensort($a, $b) {
        return strlen($b)-strlen($a);
    }

    function get_all_weapon_types(&$pdo) {
        $query = $pdo->query("SELECT * FROM weapon");
        $weapon_types = [];

        while ($row = $query->fetch()) {
            $weapon_types[$row['weapon_id']] = $row['weapon_type'];
        }

        usort($weapon_types, 'lensort');

        return $weapon_types;
    }

    function extract_quantity_word($str) {
        if (stripos($str, 'octuple') !== FALSE) {
            return 8;
        } elseif (stripos($str, 'sextuple') !== FALSE) {
            return 6; 
        } elseif (stripos($str, 'quintuple') !== FALSE) {
            return 5;
        } elseif (stripos($str, 'quad') !== FALSE) {
            return 4;
        } elseif (stripos($str, 'triple') !== FALSE) {
            return 3;
        } elseif (stripos($str, 'dual') !== FALSE) {
            return 2;
        }
    }

    function get_max_len_in_array($arr) {
        return array_reduce($arr,'maxlen');
    }

    function maxlen($k,$v) {
        if (strlen($k) > strlen($v)) return $k;
        return $v;
    }

    function get_all_weapon_types_and_ammo(&$pdo, $line) {
        $new_line = strtolower($line);
        $weapon_types = get_all_weapon_types($pdo);
        $change = TRUE;
        $actual_weapon_types = [];

        while ($change) {
            $change = FALSE;
            $possible_types = [];
            foreach ($weapon_types as $weapon_type) {
                if (stripos($new_line, $weapon_type) !== FALSE) {
                    $possible_types[] = $weapon_type;
                }
            }

            if (sizeof($possible_types) > 0) {
                $weapon_type = get_max_len_in_array($possible_types);
                $ammo = stripos($line, $weapon_type) > 10 ? get_float_value_from_line(substr($new_line, stripos($new_line, $weapon_type)-5, strlen($weapon_type)+7)): 0;
                $new_line = str_replace(strtolower($weapon_type), '', $new_line);
                $actual_weapon_types[] = array(
                    'ammo' => $ammo,
                    'weapon_type' => $weapon_type
                );
                $change = TRUE;
                $possible_types = [];
            }
        }
        return $actual_weapon_types;
    }

    function uppercase_string_with_colon($str) {
        $arr = explode(':', $str);
        foreach ($arr as $key => $index) {
            $arr[$key] = ucwords($index);
        }
        return implode(':', $arr);
    }

    function ingest_armament(&$pdo, $armament) {
        $weapon_types = get_all_weapon_types($pdo);
        $weapon_types_regex = "/(";
        foreach ($weapon_types as $weapon_type) {
            $weapon_types_regex .= $weapon_type . "|";
        }
        $weapon_types_regex = substr($weapon_types_regex, 0, -1) . ")/i";
        $armament = implode("\n", $armament);
        $armament = explode("\n", $armament);
        $new_armament = [];
        $state = 'new';
        $current_emplacement = [];

        foreach ($armament as $line) {
            if (preg_match($weapon_types_regex, $line) === 1) {
                $line = depluralise(trim($line));
                $new_armament[] = $current_emplacement;
                $current_emplacement = [];
                $state = 'existing';
                $firelink_pos = stripos($line, 'firelink');
                $current_emplacement['firelink'] = $firelink_pos === FALSE ? 0 : get_float_value_from_line(substr($line, $firelink_pos));
                $line = trim(preg_replace('/firelink/i', '', $line), " -(){}[]:\t\n\r\0\x0B");
                $current_emplacement['battery_size'] = (stripos($line, 'batter') !== FALSE && stripos($line, 'main battery') === False) ? 2 : 1 ;
                $battery_size = extract_quantity_word($line);
                $current_emplacement['battery_size'] = $battery_size > $current_emplacement['battery_size'] ? $battery_size : $current_emplacement['battery_size'];
                if (stripos($line, 'long range') === FALSE && stripos($line, 'range') !== FALSE) {
                    $current_emplacement['weapon_range'] = get_float_value_from_line(substr($line, stripos($line, 'range')));
                } else {
                    $current_emplacement['weapon_range'] = NULL;
                }
                $current_emplacement['quantity'] = get_float_value_from_line($line);
                $current_emplacement['weapon'] = get_all_weapon_types_and_ammo($pdo, $line);

                $line = trim($line, " -0..9(){}[]:\t\n\r\0\x0B");
                $line = preg_replace($weapon_types_regex, ":", $line);
                foreach ($current_emplacement['weapon'] as $weapon) {
                    $line = str_replace($weapon['ammo'], '', $line);
                }
                for ($i = 0; $i < 10; $i++) {
                    $line = str_replace(" : ", ":", $line);
                    $line = str_replace(": ", ":", $line);
                    $line = str_replace(" :", ":", $line);
                    $line = preg_replace('/\:\S*\:/', ':', $line);
                }
                $line = preg_replace('/(dual|triple|quad|quintuple|sextuple|octuple)/i', "", $line);
                $line = trim($line);
                $current_emplacement['weapon_type'] = uppercase_string_with_colon($line);
                $current_emplacement['direction'] = '';
            } elseif ($state == 'existing' && get_float_value_from_line($line) > 0) {
                $value = get_float_value_from_line($line);
                $direction = ucwords(strtolower(trim($line, " -0..9(){}[]:\t\n\r\0\x0B")));
                $temp_armament = $current_emplacement;
                $temp_armament['quantity'] = $value;
                $temp_armament['direction'] = $direction;
                $current_emplacement['quantity'] -= $value;
                $new_armament[] = $temp_armament;
            }
        }

        $new_armament[] = $current_emplacement;
        unset($new_armament[0]);

        foreach ($new_armament as $key => $emplacement) {
            if ($emplacement['quantity'] == 0) {
                unset($new_armament[$key]);
            }
        }

        return $new_armament;
    }

    function get_float_value_from_line($line) {
        return floatval(remove_comma_from_str(trim($line, " -/-:\t\n\r\0\x0Ba..zA..Z[]{}()")));
    }

    function get_unit_type(&$pdo, $unit_name) {
        $query = $pdo->query("SELECT ut.type_description FROM unit_type AS ut JOIN unit AS u ON ut.unit_type = u.unit_type WHERE name LIKE '%$unit_name%' OR alias LIKE '%$unit_name%'");

        if ($query->rowCount() === 1) {
            $row = $query->fetch();
            return $row['type_description'];
        } else {
            return FALSE;
        }
    }

    function get_consumables($line) {
        $years_pos = stripos($line, 'year');
        $months_pos = stripos($line, 'month');
        $days_pos = stripos($line, 'day');
        $years = 0;
        $months = 0;
        $days = 0;

        if ($years_pos !== False) {
            $years = get_float_value_from_line($line);
        } elseif ($months_pos !== False) {
            $months = get_float_value_from_line(substr($line, $years_pos));
        } elseif ($days_pos !== False) {
            $days = get_float_value_from_line(substr($line, $months_pos));
        }

        $days += $years * 365 + $months * 30;
        return $days;
    }

    function extract_complement_from_str($str, &$complement, $type) {
        $value = get_float_value_from_line($str);
        $value += isset($complement[$type]) ? $complement[$type] : 0;
        $value == 0 ?  : $complement[$type] = $value;
    }

    function ingest_complement(&$pdo, $complement) {
        $complement = implode("\n", $complement);
        $complement = explode("\n", $complement);
        $new_complement = [];

        foreach ($complement as $line) {
            $type = ucwords(strtolower(depluralise(trim($line, " -:\t\n\r\0\x0B0..9(){}[]"))));

            if (stripos($line, 'cargo') !== FALSE || stripos($line, 'ton') !== FALSE) {
                $value = get_float_value_from_line($line);
                $value == 0 ?  : $new_complement['Cargo Capacity'] = $value;
            } elseif (stripos($line, 'consumables') !== FALSE || stripos($line, 'year') !== FALSE || stripos($line, 'months') !== FALSE || stripos($line, 'day') !== FALSE) {
                $value = get_consumables($line);
                $value == 0 ?  : $new_complement['Consumables'] = $value;
            } elseif (stripos($line, 'passenger') !== False || stripos($line, 'troop') !== False) {
                extract_complement_from_str($line, $new_complement, 'Passenger');
            } elseif (stripos($line, 'Modular Garrison') !== False) {
                extract_complement_from_str($line, $new_complement, 'Modular Garrison');
            } elseif (stripos($line, 'juggernaut') !== False) {
                extract_complement_from_str($line, $new_complement, 'Juggernaut');
            } elseif (stripos($line, 'large vehicle') !== False) {
                extract_complement_from_str($line, $new_complement, 'Large Vehicle');
            } elseif (stripos($line, 'speeder') !== False || stripos($line, 'speeder bike') !== False) {
                extract_complement_from_str($line, $new_complement, 'Speeder');
            } elseif (stripos($line, 'medium vehicle') !== False) {
                extract_complement_from_str($line, $new_complement, 'Medium Vehicle');
            } elseif (stripos($line, 'small vehicle') !== False) {
                extract_complement_from_str($line, $new_complement, 'Small Vehicle');
            } elseif (stripos($line, 'fighter') !== False) {
                extract_complement_from_str($line, $new_complement, 'Starfighter');
            } elseif (stripos($line, 'escape pod') !== False) {
                extract_complement_from_str($line, $new_complement, 'Escape Pod');
            } elseif (in_array($type, generate_type_list($pdo))) {
                $new_complement[$type] = get_float_value_from_line($line);
            }
        }

        return $new_complement;
    }

    function ingest_crew($crew) {
        $new_crew = [];

        foreach ($crew as $line) {
            $value = get_float_value_from_line($line);
            $type = ucwords(strtolower(depluralise(trim($line, " ,:\t\n\r\0\x0B0..9(){}[]"))));

            $value == 0 ? : $new_crew[$type] = $value;
        }
        return $new_crew;
    }

    function ingest_shield_hull_value(&$stats, $str, $measure) {
        $stats[$measure] = get_float_value_from_line($str);
    }

    function extract_link(&$stats, $str) {
        $stats['wiki_link'] = isset($stats['wiki_link']) ? $stats['wiki_link'] : trim(preg_replace('/wiki link/i', '', $str), " \t\n\r\0\x0B:");
    }

    function extract_type(&$stats, $str) {
        $stats['unit_type'] = isset($stats['unit_type']) ? $stats['unit_type'] : get_float_value_from_line($str);
    }

    function ingest_unit_from_data(&$pdo, $unit_data, $unit_id) {
        $unit = [];
        $complement = [];
        $armament = [];
        $crew = [];
        $state = 'normal';
        foreach ($unit_data as $line) {
            if (stripos($line, 'length') !== FALSE || stripos($line, 'long') !== FALSE) {
                $unit['length'] = get_float_value_from_line($line);
            } elseif (stripos($line, 'height') !== FALSE) {
                $unit['height'] = get_float_value_from_line($line);
            } elseif (stripos($line, 'width') !== FALSE) {
                $unit['width'] = get_float_value_from_line($line);
            } elseif (stripos($line, 'mglt') !== FALSE || stripos($line, 'kmh') !== FALSE || stripos($line, 'km/h') !== FALSE) {
                extract_speeds($unit, $line);
            } elseif (stripos($line, 'hyperdrive') !== FALSE || stripos($line, 'backup') !== FALSE) {
                extract_hyperdrive($unit, $line);
            } elseif (stripos($line, 'wiki link') !== FALSE) {
                extract_link($unit, $line);
            } elseif (stripos($line, 'unit type') !== FALSE) {
                extract_type($unit, $line);
            } elseif (stripos($line, 'Altitude') !== FALSE) {
                $unit['max_height'] = get_float_value_from_line($line);
            } elseif (stripos($line, 'shield') !== FALSE || stripos($line, 'hull') !== FALSE) {
                extract_durability($unit, $line);

                if (stripos($line, 'sbd') !== FALSE) {
                    $unit['sbd'] = get_float_value_from_line($line);
                } elseif (stripos($line, 'ru') !== FALSE) {
                    $unit['ru'] = get_float_value_from_line($line);
                }
            } elseif (stripos($line, 'armament') !== FALSE) {
                $state = 'armament';
                $armament[] = $line;
            } elseif (stripos($line, 'complement') !== FALSE) {
                $state = 'complement';
                $complement[] = $line;
            } elseif (stripos($line, 'crew') !== FALSE) {
                $state = 'crew';
                $crew[] = $line;
            } elseif (stripos($line, 'consumable') !== FALSE) {
                $complement[] = $line;
            } elseif (stripos($line, 'consumable') !== FALSE || stripos($line, 'cargo') !== FALSE || stripos($line, 'passenger') !== FALSE) {
                $complement[] = $line;
            } elseif ($state == 'armament') {
                $armament[] = $line;
            } elseif ($state == 'complement') {
                $complement[] = $line;
            } elseif ($state == 'crew') {
                $crew[] = $line;
            }
        }

        $unit_types = generate_type_list($pdo);

        $unit['unit_id'] = $unit_id;
        $unit['armament'] = ingest_armament($pdo, $armament);
        $unit['complement'] = ingest_complement($pdo, $complement);
        $unit['crew'] = ingest_crew($crew);
        $unit['type_description'] = isset($unit_type) ? $unit_types[$unit['unit_type']] : NULL ;

        foreach (array('uc_limit', 'max_height', 'wiki_link',  'hyperdrive', 'backup', 'kmh', 'mglt', 'length', 'height', 'width', 'sbd', 'ru', 'shield', 'hull', 'notes', 'modslots', 'name', 'alias', 'price', 'points', 'is_special') as $stat) {
            $unit[$stat] = isset($unit[$stat]) ? $unit[$stat] : NULL;
        }

        foreach (array('skills', 'in_shops', 'complement', 'armament', 'crew') as $stat) {
            $unit[$stat] = isset($unit[$stat]) ? $unit[$stat] : [];
        }

        return $unit;
    }

    function generate_armament_list(&$pdo) {
        $armament_list = [];
        $armament_query = $pdo->query("SELECT armament_id, ammo, weapon_id FROM armament");

        while ($row = $armament_query->fetch()) {
            if (array_key_exists($row['armament_id'], $armament_list)) {
                $armament_list[$row['armament_id']][] = array('ammo' => $row['ammo'], 'weapon_id' => $row['weapon_id']);
            } else {
                $armament_list[$row['armament_id']] = array();
                $armament_list[$row['armament_id']][] = array('ammo' => $row['ammo'], 'weapon_id' => $row['weapon_id']);
            }
        }
        
        return $armament_list;
    }

    function update_unit_complement(&$pdo, $unit_id, $complement, $is_crew = 0) {
        $add_crew_query = 'INSERT INTO unit_complement VALUES ';
        
        foreach ($complement as $stat => $value) {
            $crew_query = $pdo->query("SELECT * FROM complement WHERE alias LIKE '$stat' AND is_crew = '$is_crew'");

            if ($crew_query->rowCount() == 0) {
                $pdo->query("INSERT INTO complement(alias, is_crew) VALUES ('$stat', '$is_crew')");
            }

            $crew_query = $pdo->query("SELECT complement_id FROM complement WHERE alias LIKE '$stat' AND is_crew = '$is_crew'");
            if ($crew_query->rowCount() == 1) {
                $row = $crew_query->fetch();
                $complement_id = $row['complement_id'];
                $crew_query = $pdo->query("SELECT * FROM unit_complement WHERE complement_id = '$complement_id' AND unit_id = '$unit_id'");
                if ($crew_query->rowCount() == 1) {
                    $pdo->query("UPDATE unit_complement SET quantity='$value' WHERE complement_id = '$complement_id' AND unit_id = '$unit_id'");
                } else $add_crew_query .= "('$complement_id', '$value', '$unit_id'), ";
            }
        }

        if (strlen($add_crew_query) > 40) {
            $pdo->query(substr($add_crew_query, 0, -2));
        }
    }

    function update_unit_armament(&$pdo, $unit_id, $armament) {
        $armament_list = generate_armament_list($pdo);
        $all_armament_query = "INSERT INTO unit_armament VALUES ";

        foreach ($armament as $emplacement) {
            foreach ($emplacement['weapon'] as $key => $weapon) {
                $armament_query = $pdo->query("SELECT weapon_id FROM weapon WHERE weapon_type LIKE '" . $weapon['weapon_type'] . "'");

                if ($armament_query->rowCount() == 1) {
                    $emplacement['weapon'][$key]['weapon_id'] = $armament_query->fetch()['weapon_id'];
                    unset($emplacement['weapon'][$key]['weapon_type']);
                } else {
                    echo "WEAPON NOT FOUND: " . $weapon['weapon_type'] . "<br  />";
                    continue 2;
                }
            }

            if (in_array($emplacement['weapon'], $armament_list)) {
                $armament_id = array_keys($armament_list, $emplacement['weapon'])[0];
            } else {
                $new_armament_id = array_key_last($armament_list) + 1;
                $weapon_query = "INSERT INTO armament VALUES ";
                foreach ($emplacement['weapon'] as $weapon) {
                    $weapon_query .= "('$new_armament_id', '" . $weapon['ammo'] . "', '" . $weapon['weapon_id'] . "'), ";
                }
                $weapon_query = substr($weapon_query, 0, -2);
                $pdo->query($weapon_query);
                
                $armament_list = generate_armament_list($pdo);
                $armament_id = $new_armament_id;
            }


            $battery_size = $emplacement['battery_size'];
            $weapon_range = not_null($emplacement['weapon_range']) ? $emplacement['weapon_range'] : 'NULL' ;
            $firelink = $emplacement['firelink'];
            $direction = $emplacement['direction'];
            $quantity = $emplacement['quantity'];
            $weapon_type = $emplacement['weapon_type'];
            $weapon_range_search = $weapon_range === 'NULL' ? 'weapon_range IS NULL' : "weapon_range = '$weapon_range'";

            $emplacement_query = $pdo->query("SELECT * FROM unit_armament WHERE direction = '$direction' AND firelink = '$firelink' AND battery_size = '$battery_size' AND armament_id = '$armament_id' AND unit_id = '$unit_id' AND " . $weapon_range_search);
            if ($emplacement_query->rowCount() >= 1) {
                $pdo->query("UPDATE unit_armament SET quantity='$quantity', weapon_type='$weapon_type' WHERE direction = '$direction' AND firelink = '$firelink' AND battery_size = '$battery_size' AND armament_id = '$armament_id' AND unit_id = '$unit_id' AND " . $weapon_range_search);
            } else $all_armament_query .= "('$unit_id', '$armament_id', '$battery_size', $weapon_range, '$firelink', '$weapon_type', '$quantity', '$direction'), ";
        }

        if (strlen($all_armament_query) > 40) {
            $pdo->query(substr($all_armament_query, 0, -2));
        }
    }

    function update_unit_skills(&$pdo, $unit_id, $complement) {
        
    }

    function update_unit_shops(&$pdo, $unit_id, $complement) {
        
    }

    function update_unit(&$pdo, $unit_id, $unit) {
        $query = "UPDATE unit SET ";

        foreach ($unit as $stat => $value) {
            if ($stat == 'armament') {
                update_unit_armament($pdo, $unit_id, $value);
            } elseif ($stat == 'complement') {
                update_unit_complement($pdo, $unit_id, $value);
            } elseif ($stat == 'skills') {
                update_unit_skills($pdo, $unit_id, $value);
            } elseif ($stat == 'crew') {
                update_unit_complement($pdo, $unit_id, $value, 1);
            } elseif ($stat == 'in_shops') {
                update_unit_shops($pdo, $unit_id, $value);
            } elseif (!in_array($stat, array('type_description', 'unit_id'))) {
                if (not_null($value)) {
                    $query .= "$stat = '$value', ";
                }
            }
        }

        $query = substr($query, 0, -2);
        $query .= " WHERE unit_id = $unit_id";

        $pdo->query($query);
    }