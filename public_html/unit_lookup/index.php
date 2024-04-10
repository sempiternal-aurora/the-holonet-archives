<?php
    require_once '../../php/functions.php';
    require_once DOCUMENT_ROOT . 'php/header.php';

    $max_min_query = $pdo->query("SELECT MAX(price), MAX(points), 
        MAX(modslots), 
        MAX(hyperdrive), 
        MAX(backup), 
        MAX(mglt), 
        MAX(kmh), 
        MAX(uc_limit), 
        MAX(length), 
        MAX(width), 
        MAX(height),
        MAX(sbd),
        MAX(ru),
        MIN(price), 
        MIN(points), 
        MIN(modslots), 
        MIN(hyperdrive), 
        MIN(backup), 
        MIN(mglt), 
        MIN(kmh), 
        MIN(uc_limit), 
        MIN(length), 
        MIN(width), 
        MIN(height),
        MIN(sbd),
        MIN(ru)
        FROM unit");

    $max_min_array = $max_min_query->fetch();

    $max_price = $max_min_array['MAX(price)'];
    $max_points = $max_min_array['MAX(points)'];
    $max_modslots = $max_min_array['MAX(modslots)'];
    $max_hyperdrive = $max_min_array['MAX(hyperdrive)'];
    $max_backup = $max_min_array['MAX(backup)'];
    $max_mglt = $max_min_array['MAX(mglt)'];
    $max_kmh = $max_min_array['MAX(kmh)'];
    $max_uc_limit = $max_min_array['MAX(uc_limit)'];
    $max_length = $max_min_array['MAX(length)'];
    $max_width = $max_min_array['MAX(width)'];
    $max_height = $max_min_array['MAX(height)'];
    $max_sbd = $max_min_array['MAX(sbd)'];
    $max_ru = $max_min_array['MAX(ru)'];
    $min_price = $max_min_array['MIN(price)'];
    $min_points = $max_min_array['MIN(points)'];
    $min_modslots = $max_min_array['MIN(modslots)'];
    $min_hyperdrive = $max_min_array['MIN(hyperdrive)'];
    $min_backup = $max_min_array['MIN(backup)'];
    $min_mglt = $max_min_array['MIN(mglt)'];
    $min_kmh = $max_min_array['MIN(kmh)'];
    $min_uc_limit = $max_min_array['MIN(uc_limit)'];
    $min_length = $max_min_array['MIN(length)'];
    $min_width = $max_min_array['MIN(width)'];
    $min_height = $max_min_array['MIN(height)'];
    $min_sbd = $max_min_array['MIN(sbd)'];
    $min_ru = $max_min_array['MIN(ru)'];

    $all_columns_query = $pdo->query("DESCRIBE unit");

    $columns = [];

    while ($row = $all_columns_query->fetch()) {
        if (!in_array($row['Field'], array('unit_id', 'alias', 'notes'))) {
            $columns[] = $row['Field'];
        }
    }

    $get_keys = array('name', 'is_special', 'type', 'modslots-min', 'modslots-max', 'price-min', 'price-max', 'shield', 'hull', 'points-min', 'points-max', 'hyperdrive-min', 'hyperdrive-max', 'backup-min', 'backup-max', 'mglt-min', 'mglt-max', 'kmh-min', 'kmh-max', 'uc-limit-min', 'uc-limit-max', 'length-min', 'length-max', 'width-min', 'width-max', 'sbd-min', 'sbd-max', 'ru-min', 'ru-max');

    $sanitised_get_variables = [];

    $counter = 0;

    $unit_query = 'SELECT * FROM unit WHERE ';

    if (isset($_GET['name'])) {
        foreach ($get_keys as $key) {
            if (isset($_GET[$key]) && $_GET[$key] != '') {
                $sanitised_get_variables[$key] = sanitise_string($pdo, $_GET[$key]);
                if (str_contains($key, "-")) {
                    $pos_m_dash = strrpos($key, "-");
                    $max_or_min = strtoupper(substr($key, $pos_m_dash+1));
                    $variable = str_replace("-", '_', substr($key, 0, $pos_m_dash));
                    if ($max_min_array["$max_or_min($variable)"] == $sanitised_get_variables[$key]) {
                        unset($sanitised_get_variables[$key]);
                    }
                } elseif (in_array($key, array('shield', 'hull', 'type'))) {
                    if ($sanitised_get_variables[$key] == 0) {
                        unset($sanitised_get_variables[$key]);
                    }
                } elseif ($key == 'is_special') {
                    if ($sanitised_get_variables[$key] == 2) {
                        unset($sanitised_get_variables[$key]);
                    }
                }
            }
        }

        foreach ($sanitised_get_variables as $key => $value) {
            if (str_contains($key, 'min')) {
                $pos_m_dash = strrpos($key, "-");
                $variable = str_replace("-", '_', substr($key, 0, $pos_m_dash));
                $unit_query .= "$variable >= $value AND ";
                $counter += 1;
            } elseif (str_contains($key, 'max')) {
                $pos_m_dash = strrpos($key, "-");
                $variable = str_replace("-", '_', substr($key, 0, $pos_m_dash));
                $unit_query .= "$variable <= $value AND ";
                $counter += 1;
            } elseif ($key == 'type') {
                $unit_query .= "unit_type=$value AND ";
                $counter += 1;
            } elseif ($key == 'name') {
                $unit_query .= "(name LIKE '%$value%' OR alias LIKE '%$value%') AND ";
                $counter += 1;
            } elseif ($key == 'is_special' && in_array($value, array(0, 1))) {
                $unit_query .= "is_special=$value AND ";
                $counter += 1;
            }
        }
    }

    
    if ($counter >= 1) {
        $unit_query = substr($unit_query, 0, -4);
    } else {
        $unit_query = substr($unit_query, 0, -6);
    }

    if (isset($_GET['sort'])) {
        $sort_column = sanitise_string($pdo, $_GET['sort']);
        $sort_order = sanitise_string($pdo, $_GET['sort-dir']);
        if (in_array($sort_column, $columns)) {
            $unit_query .= " ORDER BY $sort_column";
            if (in_array($sort_order, array("ASC", "DESC"))) {
                $unit_query .= " $sort_order";
            }
        } else {
            $unit_query .= " ORDER BY access DESC";
        }
    }

    echo <<<_END
                <script src='unit_lookup.js'></script>
                <div class='search'>
                    <form action='' data-ajax='false' method='GET'>
                        <div class='ui-field-contain search-input'>
                            <label for='name'>Unit Name</label>
                            <input name='name' id='unit-name-input' type='search'  />
                        </div>
                        <div class='ui-field-contain search-input'>
                            <label for='is_special'>Special?</label>
                            <select name='is_special'>
                                <option value='2'>--</option>
                                <option value='1'>Yes</option>
                                <option value='0'>No</option>
                            </select>
                        </div>
    _END;

    echo "<div class='ui-field-contain search-input'><label for='type' class='select'>Unit Type</label><select name='type'>";
    $type_query = $pdo->query("SELECT * FROM unit_type");
    
    $types = [];

    echo "<option value='0'>--</option>";

    while ($row = $type_query->fetch()) {
        $types[$row['unit_type']] = $row['type_description'];
    }

    foreach ($types as $num => $descriptor) {
        echo "<option value='$num'>$descriptor</option>";
    }

    echo "</select></div>";


    echo <<<_END
                        <label for='sort' class='ui-hidden-accessible'>Sort By</label>
                        <label for='sort-dir' class='ui-hidden-accessible'>Sort Direction</label>
                        <div class='centre search-input'>
                            <span class='sort-by'>Sort By</span>
                            <select name='sort' data-inline='true'>
                                <option>--</option>
    _END;

    foreach ($columns as $column) {
        echo "<option value='$column'>" . ucwords(str_replace("_", " ", $column)) . "</option>";
    }

    echo "</select>";

    echo <<<_END
                            <select name='sort-dir' data-inline='true'>
                                <option>--</option>
                                <option value='ASC'>Ascending</option>
                                <option value='DESC'>Descending</option>
                            </select>
                        </div>
                        <div class='show-button'>
                            <button class='ui-btn ui-corner-all ui-btn-inline form-button' id='show-advanced' type='button'>Show Advanced</button>
                            <input type='submit' name='submit' data-inline='true' data-ajax='false' value='Search'  />
                            <a href='../unit_lookup?r=$randstr' data-ajax='false' class='ui-btn ui-corner-all ui-btn-inline form-button'>Clear Filters</a>
                        </div>
                        <div class='advanced search-input' style='display: none;'>
    _END;

    echo <<<_END
                            <div class='ui-field-contain'>
                                <div data-role='rangeslider'>
                                    <label for='modslots-min'>Modslots</label>
                                    <input name="modslots-min" id="modslots-min" min="$min_modslots" max="$max_modslots" value="$min_modslots" type="range" />
                                    <label for="modslots-max">Modslots</label>
                                    <input name="modslots-max" id="modslots-max" min="$min_modslots" max="$max_modslots" value="$max_modslots" type="range" />
                                </div>
                            </div>
                            <div class='ui-field-contain'>
                                <div data-role='rangeslider' id='price-rangeslider'>
                                    <label for='price-min'>Price</label>
                                    <input name="price-min" id="price-min" min="$min_price" max="$max_price" value="$min_price" type="range" />
                                    <label for="price-max">Price</label>
                                    <input name="price-max" id="price-max" min="$min_price" max="$max_price" value="$max_price" type="range" />
                                </div>
                            </div>

    _END;

    echo "<div class='ui-field-contain'><label for='shield' class='select'>Shield Strength</label><select name='shield'>";
    $strength_query = $pdo->query("SELECT * FROM strength");
    
    $strength = [];

    while ($row = $strength_query->fetch()) {
        $strength[$row['strength_no']] = $row['str_description'];
    }

    echo "<option value='0'>--</option>";

    foreach ($strength as $num => $descriptor) {
        echo "<option value='$num'>$descriptor</option>";
    }

    echo "</select></div>";

    echo "<div class='ui-field-contain'><label for='hull' class='select'>Hull Strength</label><select name='hull'>";

    echo "<option value='0'>--</option>";

    foreach ($strength as $num => $descriptor) {
        echo "<option value='$num'>$descriptor</option>";
    }

    echo "</select></div>";

    echo <<<_END
                            <div class='ui-field-contain'>
                                <div data-role='rangeslider'>
                                    <label for='points-min'>Points</label>
                                    <input name="points-min" id="points-min" min="$min_points" max="$max_points" value="$min_points" step='0.25' type="range" />
                                    <label for="points-max">Points</label>
                                    <input name="points-max" id="points-max" min="$min_points" max="$max_points" value="$max_points" step='0.25' type="range" />
                                </div>
                            </div>
                            <div class='ui-field-contain'>
                                <div data-role='rangeslider'>
                                    <label for='hyperdrive-min'>Hyperdrive</label>
                                    <input name="hyperdrive-min" id="hyperdrive-min" min="$min_hyperdrive" max="$max_hyperdrive" value="$min_hyperdrive" step='0.1' type="range" />
                                    <label for="hyperdrive-max">Hyperdrive</label>
                                    <input name="hyperdrive-max" id="hyperdrive-max" min="$min_hyperdrive" max="$max_hyperdrive" value="$max_hyperdrive" step='0.1' type="range" />
                                </div>
                            </div>
                            <div class='ui-field-contain'>
                                <div data-role='rangeslider'>
                                    <label for='backup-min'>Backup Hyperdrive</label>
                                    <input name="backup-min" id="backup-min" min="$min_backup" max="$max_backup" value="$min_backup" step='0.1' type="range" />
                                    <label for="backup-max">Backup Hyperdrive</label>
                                    <input name="backup-max" id="backup-max" min="$min_backup" max="$max_backup" value="$max_backup" step='0.1' type="range" />
                                </div>
                            </div>
                            <div class='ui-field-contain'>
                                <div data-role='rangeslider'>
                                    <label for='mglt-min'>Speed (MGLT)</label>
                                    <input name="mglt-min" id="mglt-min" min="$min_mglt" max="$max_mglt" value="$min_mglt" type="range" />
                                    <label for="mglt-max">Speed (MGLT)</label>
                                    <input name="mglt-max" id="mglt-max" min="$min_mglt" max="$max_mglt" value="$max_mglt" type="range" />
                                </div>
                            </div>
                            <div class='ui-field-contain'>
                                <div data-role='rangeslider'>
                                    <label for='kmh-min'>Speed (km/h)</label>
                                    <input name="kmh-min" id="kmh-min" min="$min_kmh" max="$max_kmh" value="$min_kmh" type="range" />
                                    <label for="kmh-max">Speed (km/h)</label>
                                    <input name="kmh-max" id="kmh-max" min="$min_kmh" max="$max_kmh" value="$max_kmh" type="range" />
                                </div>
                            </div>
                            <div class='ui-field-contain'>
                                <div data-role='rangeslider'>
                                    <label for='uc-limit-min'>UC Limit</label>
                                    <input name="uc-limit-min" id="uc-limit-min" min="$min_uc_limit" max="$max_uc_limit" value="$min_uc_limit" type="range" />
                                    <label for="uc-limit-max">UC-limit</label>
                                    <input name="uc-limit-max" id="uc-limit-max" min="$min_uc_limit" max="$max_uc_limit" value="$max_uc_limit" type="range" />
                                </div>
                            </div>
                            <div class='ui-field-contain'>
                                <div data-role='rangeslider'>
                                    <label for='length-min'>Length</label>
                                    <input name="length-min" id="length-min" step='0.01' min="$min_length" max="$max_length" value="$min_length" type="range" />
                                    <label for="length-max">Length</label>
                                    <input name="length-max" id="length-max" step='0.01' min="$min_length" max="$max_length" value="$max_length" type="range" />
                                </div>
                            </div>
                            <div class='ui-field-contain'>
                                <div data-role='rangeslider'>
                                    <label for='width-min'>Width</label>
                                    <input name="width-min" id="width-min" step='0.01' min="$min_width" max="$max_width" value="$min_width" type="range" />
                                    <label for="width-max">Width</label>
                                    <input name="width-max" id="width-max" step='0.01' min="$min_width" max="$max_width" value="$max_width" type="range" />
                                </div>
                            </div>
                            <div class='ui-field-contain'>
                                <div data-role='rangeslider'>
                                    <label for='height-min'>Height</label>
                                    <input name="height-min" id="height-min" step='0.01' min="$min_height" max="$max_height" value="$min_height" type="range" />
                                    <label for="height-max">Height</label>
                                    <input name="height-max" id="height-max" step='0.01' min="$min_height" max="$max_height" value="$max_height" type="range" />
                                </div>
                            </div>
                            <div class='ui-field-contain'>
                                <div data-role='rangeslider'>
                                    <label for='sbd-min'>SBD</label>
                                    <input name="sbd-min" id="sbd-min" min="$min_sbd" max="$max_sbd" value="$min_sbd" type="range" />
                                    <label for="sbd-max">SBD</label>
                                    <input name="sbd-max" id="sbd-max" min="$min_sbd" max="$max_sbd" value="$max_sbd" type="range" />
                                </div>
                            </div>
                            <div class='ui-field-contain'>
                                <div data-role='rangeslider'>
                                    <label for='ru-min'>RU</label>
                                    <input name="ru-min" id="ru-min" min="$min_ru" max="$max_ru" value="$min_ru" type="range" />
                                    <label for="ru-max">RU</label>
                                    <input name="ru-max" id="ru-max" min="$min_ru" max="$max_ru" value="$max_ru" type="range" />
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
    _END;


    echo "<div class='shop'><ul>";
    if (isset($unit_query)) {
        $unit_query_result = $pdo->query($unit_query);

        while ($row = $unit_query_result->fetch()) {
            echo display_shop_unit($row);
        }
    }

    echo "</ul></div></div>";

    include_once DOCUMENT_ROOT . 'php/footer.php';