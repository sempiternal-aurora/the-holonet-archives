<?php
    require_once '../../php/functions.php';
    require_once DOCUMENT_ROOT . 'php/header.php';

    if (isset($_SESSION['user'])) {
        destroy_session_completely();
        echo <<<_END
                    <br  />
                    <div class='centre'> You have been logged out. Please
                        <a data-transition='slide' href='index.php?r=$randstr'>click here</a>
                        to refresh the screen.
                    </div></div>
        _END;
    } else {
        echo "<div class='centre'>You cannot log out because you are not logged in.</div></div>";
    }

    include_once DOCUMENT_ROOT . '/php/footer.php';