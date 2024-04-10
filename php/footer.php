<?php
    require_once 'functions.php';

    echo <<<_END
                <div data-role='footer'>
                    <h4 class='centre'>Web App Developed by Myria: <a href='{$C('WEBSITE_ROOT')}/contact?r=$randstr'>Contact me</a></h4>
                    <div class='centre'>By using this website, you agree to the terms of service listed here: <a href='{$C('WEBSITE_ROOT')}/terms_of_use'>{$C('WEBSITE_ROOT')}/terms_of_use</a>
                </div>
            </div>
        </body>
    </html>
    _END;