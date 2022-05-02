<?php
    require_once '../../php/functions.php';
    require_once DOCUMENT_ROOT . 'php/header.php';

    if (isset($_GET['uid'])) {
        $unit_id = sanitise_string($pdo, $_GET['uid']);

        $is_valid_id = verify_id($pdo, $unit_id, 'unit');
        
        if ($is_valid_id == '') { 
            $stats = get_unit_stats($pdo, $unit_id);
            echo "<br  />";

            unset($stats['unit_id']);

            echo <<<_END
                            <div class='unit'>
            _END;
            
            display_unit_stats($stats);

            echo <<<_END
                            </div>
                        </div>
            _END;
        } else {
            echo "<h4 class='centre'>$is_valid_id</h4></div>";
        }
    }
    else {
        echo "<h4 class='centre'>Please specify a unit to lookup stats for.</h4></div>";
    }

    include_once DOCUMENT_ROOT . 'php/footer.php';