<?php
    require_once '../../php/functions.php';
    
    require_once DOCUMENT_ROOT . 'php/initialise.php';
    
    display_header($title, $randstr, $logged_in_as, $logged_in, $privilege);

    $terms_of_use = file_get_contents(DOCUMENT_ROOT . 'public_html/terms_of_use/terms_of_use.txt');

    $terms_of_use = str_replace(array("*()*", "*)(*", "...***...", ".../***...", "..**..", "../**..", ".*.", "./*."), array("<ul>", "</ul>", "<h1>", "</h1>", "<h2>", "</h2>", "<h3>", "</h3>"), $terms_of_use);
    
    $terms_of_use = explode("\n", $terms_of_use);

    foreach ($terms_of_use as $key => $line) {
        $terms_of_use[$key] = str_contains($line, "- ") ? str_replace("- ", "<li>", $line) . "</li>" : $line;
        $line = $terms_of_use[$key];
        if (str_contains($line, '**A**')) {
            $open_pos = strpos($line, '**A**');
            $close_pos = strpos($line, '**-A**');
            $href = substr($line, $open_pos + 5, $close_pos - $open_pos - 5);
            $terms_of_use[$key] = substr($line, 0, $open_pos) . "<a href='$href'>$href</a>" . substr($line, $close_pos + 6);
        }
    }
    
    $terms_of_use = implode("\n", $terms_of_use);

    echo $terms_of_use;

    echo "</div>";

    include_once DOCUMENT_ROOT . 'php/footer.php';