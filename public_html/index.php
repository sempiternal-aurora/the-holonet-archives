<?php
    require_once '../php/functions.php';
    require_once DOCUMENT_ROOT . 'php/header.php';

    echo <<<_END
                <div class='centre'>
                    Welcome to The Holonet Archives<br  /><br  />
                    Here you can search through all sorts of different fictional star wars units, and;
                    <ul>
                        <li>See their stats</li>
                        <li>Compare them</li>
                        <li>See what shops they come from</li>
                        <li>And even calculate orders with them</li>
                    </ul>
                    To get started, follow one of the links above to a different part of the website!
                </div>
    _END;

    require_once DOCUMENT_ROOT . 'php/footer.php';