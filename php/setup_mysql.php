<?php
    define("MYSQL_ADMIN", ""); //the mysql admin account used for creating the database
    define("MYSQL_ADMIN_PASS", ""); //the mysql admin password, enter these before running the script, left empty so that it doesn't break the program if someone stumbles across these files

    require_once 'functions.php'; //get all necessary functions

    $tables = array( //an array containing keyed items, the key being the tables, and the arrays inside them containing the attributes for each table
        'shop' => array('shop_id', 'shop_name', 'is_special'),
        'units_in_shop' => array('shop_id', 'unit_id'),
        'user' => array('username', 'pass'),
        'user_privilege' => array('username', 'privilege_id'),
        'privilege' => array('privilege_id', 'privilege'),
        'unit' => array('unit_id', 'unit_type', 'name', 'alias', 'price', 'modslots', 'uc_limit', 'length', 'height', 'width', 'hyperdrive', 'backup', 'mglt', 'kmh', 'shield', 'hull', 'sbd', 'hbd', 'points', 'is_special'),
        'unit_type' => array('unit_type', 'type_description'),
        'strength' => array('strength_no', 'str_description'),
        'unit_skill' => array('unit_id', 'skill_id', 'value'),
        'skill' => array('skill_id', 'skill'),
        'unit_complement' => array('complement_id', 'quantity', 'unit_id'),
        'complement' => array('complement_id', 'alias', 'is_crew', 'requirement_id'),
        'unit_armament' => array('unit_id', 'armament_id', 'battery_size', 'weapon_range', 'firelink', 'weapon_type', 'quantity', 'direction'),
        'armament' => array('armament_id', 'ammo', 'weapon_id'),
        'weapon' => array('weapon_id', 'weapon_type'),
        'modification' => array('mod_id', 'name', 'modslots', 'is_refit', 'price', 'is_special'),
        'mod_requirements' => array('mod_id', 'requirement_id'),
        'requirements' => array('requirement_id', 'stat_id', 'comparator', 'value'),
        'stat_table' => array('stat_id', 'name', 'in_table')
    );

    /*
    // quick code to check that all attributes have the same name
    $all_attributes = array();

    foreach ($tables as $attributes) {
        foreach ($attributes as $attribute) {
            if (!in_array($attribute, $all_attributes)) {
                $all_attributes[] = $attribute;
            }
        }
    }

    foreach ($all_attributes as $attribute) {
        echo $attribute . ", ";
    }
    */

    //now create a table that has attributes and their sql data type
    $data_types = array(
        'shop_id' => 'SMALLINT UNSIGNED', 
        'shop_name' => 'VARCHAR(128)', 
        'unit_id' => 'SMALLINT UNSIGNED',
        'username' => 'VARCHAR(32)', 
        'pass' => 'VARCHAR(255)', 
        'privilege_id' => 'SMALLINT UNSIGNED', 
        'privilege' => 'VARCHAR(32)',
        'unit_type' => 'SMALLINT UNSIGNED', 
        'name' => 'VARCHAR(128)',
        'alias' => 'VARCHAR(32)', 
        'price' => 'BIGINT UNSIGNED', 
        'modslots' => 'TINYINT UNSIGNED', 
        'uc_limit' => 'SMALLINT UNSIGNED', 
        'length' => 'FLOAT', 
        'height' => 'FLOAT', 
        'width' => 'FLOAT', 
        'hyperdrive' => 'CHAR(4)', 
        'backup' => 'CHAR(4)', 
        'mglt' => 'TINYINT UNSIGNED', 
        'kmh' => 'SMALLINT UNSIGNED', 
        'shield' => 'TINYINT UNSIGNED', 
        'hull' => 'TINYINT UNSIGNED', 
        'sbd' => 'SMALLINT UNSIGNED', 
        'hbd' => 'SMALLINT UNSIGNED',
        'type_description' => 'VARCHAR(128)', 
        'str_description' => 'VARCHAR(128)', 
        'strength_no' => 'TINYINT UNSIGNED', 
        'skill_id' => 'TINYINT UNSIGNED', 
        'value' => 'TINYINT UNSIGNED', 
        'skill' => 'VARCHAR(32)', 
        'complement_id' => 'SMALLINT UNSIGNED', 
        'is_crew' => 'TINYINT UNSIGNED', 
        'requirement_id' => 'SMALLINT UNSIGNED', 
        'armament_id' => 'SMALLINT UNSIGNED', 
        'battery_size' => 'TINYINT UNSIGNED', 
        'weapon_range' => 'SMALLINT UNSIGNED', 
        'firelink' => 'TINYINT UNSIGNED', 
        'weapon_type' => 'VARCHAR(32)', 
        'quantity' => 'SMALLINT UNSIGNED', 
        'direction' => 'VARCHAR(32)', 
        'ammo' => 'SMALLINT UNSIGNED', 
        'weapon_id' => 'TINYINT UNSIGNED', 
        'weapon_type' => 'VARCHAR(32)', 
        'mod_id' => 'SMALLINT UNSIGNED', 
        'is_refit' => 'TINYINT UNSIGNED', 
        'stat_id' => 'SMALLINT UNSIGNED', 
        'comparator' => 'CHAR(2)', 
        'in_table' => 'VARCHAR(16)',
        'is_special' => 'TINYINT UNSIGNED',
        'points' => 'TINYINT UNSIGNED'
    );

    $primary_keys = array( //an array containing each table and it's respective primary key
        'shop' => 'shop_id',
        'units_in_shop' => array('shop_id', 'unit_id'),
        'user' => 'username',
        'user_privilege' => array('username', 'privilege_id'),
        'privilege' => 'privilege_id',
        'unit' => 'unit_id',
        'unit_type' => 'unit_type',
        'strength' => 'strength_no',
        'unit_skill' => array('unit_id', 'skill_id'),
        'skill' => 'skill_id',
        'unit_complement' => array('unit_id', 'complement_id'),
        'complement' => 'complement_id',
        'armament' => 'armament_id',
        'weapon' => 'weapon_id',
        'modification' => 'modification_id',
        'mod_requirements' => array('modification_id', 'requirement_id'),
        'requirements' => 'requirement_id',
        'stat_table' => 'stat_id'
    );

    $pdo = initialise_mysql_connection(MYSQL_HOST, MYSQL_DATABASE, MYSQL_ADMIN, MYSQL_ADMIN_PASS); //start the connection to the MySQL database (mariaDB here usually)

    drop_all_tables($pdo, MYSQL_DATABASE); //drop all tables in the database to create a clean slate to work from

    create_tables_from_keyed_array($pdo, $tables, $data_types); //create the tables now

    // now, for primary keys    
    foreach ($primary_keys as $table => $keys) { //for each of the tables
        designate_primary_keys($pdo, $table, $keys); //designate the primary keys
        if (!is_array($keys)) {
            add_auto_incrementation_to_primary_keys($pdo, $table, $keys, $data_types[$keys]); //and if there is only one key, try to make it an auto_incrementing key
        }
    }

    //Foreign Keys 2.0 (deals with cases where columns aren't in primary key)
    $foreign_keys = remove_arrays_from_array($primary_keys); //get only singular primary keys
        
    foreach ($tables as $table => $attributes) { //iterate through the tables and designate any foreign keys
        designate_foreign_keys($pdo, $table, $attributes, $foreign_keys);
    }

    