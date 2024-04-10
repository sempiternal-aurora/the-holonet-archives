<?php
    require_once '../../php/functions.php';
    require_once DOCUMENT_ROOT . 'php/header.php';

    if (isset($_GET['sid'])) {
        $shop_id = sanitise_string($pdo, $_GET['sid']);

        if ($shop_id == 47) {
            echo "<iframe class='youtube-embed-full' class='full-screen-image' src='https://www.youtube.com/embed/DJfg39WkMvE' title='YouTube video player' frameborder='0' allow='accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture' allowfullscreen></iframe></div>";
            $no_footer = TRUE;
        } else {
            $is_valid_id = validate_id($pdo, $shop_id, 'shop');
            
            if ($is_valid_id == '') { 
                $shop = get_shop_stats($pdo, $shop_id);
                increment_access($pdo, $shop_id, 'shop'); //Increment how many times the shop has been accessed for sorting reasons
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
    }
    else {
        echo "<h4 class='centre'>All Shops</h4>";
        $all_shops = fetch_all_shops($pdo);

        foreach ($all_shops as $shop) {
            echo display_shop_link($shop);
        }

        echo "</div>";
    }

    include_once DOCUMENT_ROOT . 'php/footer.php';