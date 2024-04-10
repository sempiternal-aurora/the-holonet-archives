<?php
    require_once 'functions.php';

    $pdo = initialise_mysql_connection(MYSQL_HOST, MYSQL_DATABASE, MYSQL_USER, MYSQL_PASS);

    $testing_file = fopen("testing.csv", 'w');
    fwrite($testing_file, "weapon,result\n");

    
    $armament = array("28 Multi Troop Transport", "3000000000 Passenger", "-1 B1-Series Battle Droid", "34 Tie-Series Fighter", "108 Small Ships", "3 Years 2 Months 1 Day Consumables", "3.05 Years Comsumables", "100000000000000 Metric Tons Cargo", "0.001 Metric Tons Cargo", "0.000001 Metric Tons Cargo", "0.1 Speeder Bike", "1 Prefabricated Garrison Base");
    
    foreach ($armament as $emplacement) {
        fwrite($testing_file, str_replace("\n", '\n', $emplacement).",");

        $emplacement = ingest_complement($pdo, array($emplacement));

        $stats = array("complement" => $emplacement);

        $emplacement = display_complement($stats);

        fwrite($testing_file, str_replace("Complement", "", strip_tags($emplacement)) . "\n");
    }
    
    fclose($testing_file);