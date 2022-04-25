<?php
    require_once '../../php/functions.php';
    require_once DOCUMENT_ROOT . 'php/header.php';

    if (isset($_GET['uid'])) {
        $unit_id = $_GET['uid'];
        $stats = get_unit_stats($pdo, $unit_id);
        get_unit_stats_yes($pdo, $unit_id);
        echo <<<_END
                        <div class='unit'>
        _END;
        
        display_unit_stats($stats);

        echo <<<_END
                        </div>
        _END;
    }
    else {
        echo <<<_END
                        Please specify a unit to lookup stats for.
        _END;
    }