<?php
    require_once '../../php/functions.php';
    
    require_once DOCUMENT_ROOT . 'php/initialise.php';
    
    display_header($title, $randstr, $logged_in_as, $logged_in, $privilege);

    echo <<<_END
        <div class='centre-div'>
        You can contact the administrator of this website through the following email:

        <div class='centre'><a href='mailto:myrialsarvay@gmail.com'>myrialsarvay@gmail.com</a></div>
        </div>
    _END;

    echo "</div>";

    include_once DOCUMENT_ROOT . 'php/footer.php';