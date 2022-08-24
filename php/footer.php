<?php
    require_once 'functions.php';

    $C = 'constant'; //create a variable that references the constant function

    if (!isset($no_footer)) {
        echo "<div data-role='footer'>";
        echo "<h4 class='centre'>Web App Developed by Myria: <a href='{$C('WEBSITE_ROOT')}/contact?r=$randstr'>Contact me</a></h4>";
        echo "<div class='centre'>By using this website, you agree to the terms of service listed here: <a href='{$C('WEBSITE_ROOT')}/terms_of_use'>{$C('WEBSITE_ROOT')}/terms_of_use</a>";
    }

    echo <<<_END
                    <!-- Did you ever hear the tragedy of Darth Plagueis The Wise? I thought not. It’s not a story the Jedi would tell you. It’s a Sith legend. Darth Plagueis was a Dark Lord of the Sith, so powerful and so wise he could use the Force to influence the midichlorians to create life… He had such a knowledge of the dark side that he could even keep the ones he cared about from dying. The dark side of the Force is a pathway to many abilities some consider to be unnatural. He became so powerful… the only thing he was afraid of was losing his power, which eventually, of course, he did. Unfortunately, he taught his apprentice everything he knew, then his apprentice killed him in his sleep. Ironic. He could save others from death, but not himself.-->
                </div>
            </div>
        </body>
    </html>
    _END;