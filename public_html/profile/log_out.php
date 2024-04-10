<?php
    require_once '../../php/functions.php';
    
    require_once DOCUMENT_ROOT . 'php/initialise.php';
    
    display_header($title, $randstr, $logged_in_as, $logged_in, $privilege);

    if (isset($_SESSION['user'])) {
        destroy_session_completely();
        echo <<<_END
                    <br  />
                    <div class='centre'> You have been logged out. Please
                        <a data-transition='slide' href='{$C('WEBSITE_ROOT')}/?r=$randstr'>click here</a>
                        to refresh the screen.
                    </div></div>
        _END;
    } else {
        echo "<div class='centre'>You cannot log out because you are not logged in.</div></div>";
    }

    include_once DOCUMENT_ROOT . '/php/footer.php';