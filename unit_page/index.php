<?php
    require_once '../_/php/functions.php';
    require_once DOCUMENT_ROOT . '_/php/header.php';

    if (isset($_GET['uid'])) {
        $unit_id = $_GET['uid'];
        $stats = get_unit_stats($pdo, $unit_id);
        get_unit_stats_yes($pdo, $unit_id);
        echo <<<_END
                        <div class='unit'>
        _END;
        
        display_unit_name_string($stats);

        display_unit_type_price_string($stats);

        display_dimensions_string($stats);

        display_hyperdrive_string($stats);

        foreach ($stats as $stat => $value) {
            if ($value === NULL);
            elseif ($stat == 'armament') display_armament($value);
            elseif ($stat == 'complement') display_complement($value);
            elseif ($stat == 'crew') display_crew($value);
            else display_simple_stat($stat, $value);
        }
        echo <<<_END
                        </div>
        _END;
    }
    else {
        echo <<<_END
                        Please specify a unit to lookup stats for.
        _END;
    }