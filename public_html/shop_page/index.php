<?php
    require_once '../../php/functions.php';
    require_once DOCUMENT_ROOT . 'php/header.php';

    if (isset($_GET['sid'])) {
        $shop_id = sanitise_string($pdo, $_GET['sid']);

        $is_valid_id = verify_id($pdo, $shop_id, 'shop');
        
        if ($is_valid_id == '') { 
            $shop = get_shop_stats($pdo, $shop_id);
            echo "<br  />";

            echo <<<_END
                            <div class='shop'>
            _END;
            
            display_shop($shop[0], $shop[1]);

            echo <<<_END
                            </div>
                        </div>
            _END;
        } else {
            echo "<h4 class='centre'>$is_valid_id</h4></div>";
        }
    }
    else {
        echo "<h4 class='centre'>All Shops</h4>";
        $all_shops = fetch_all_shops($pdo);

        foreach ($all_shops as $shop) {
            echo display_shop_link($shop);
        }
    }

    include_once DOCUMENT_ROOT . 'php/footer.php';