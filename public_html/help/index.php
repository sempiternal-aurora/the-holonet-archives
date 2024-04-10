<?php
    require_once '../../php/functions.php';
    require_once DOCUMENT_ROOT . 'php/header.php';

    echo <<<_END
    <div class='centre-div'>
    <h1>User Documentation</h1>
    The following consists of all documentation for the modules of the software, please consult here if you have problems with using any module.<br  /><br  />
    <h2>Login System</h2>
    The login system takes your provided password, and passes it through a hash algorithm to store it without storing the actual password.<br  /><br  />
    When you provide the password again, it is passed through the same algorithm, and the results compared to ensure that the passwords are correct.<br  /><br  />
    The hash function is designed to be unable to be reversed, but have a consistent result, so your password is never stored on our servers.<br  /><br  />
    <h3>Signing Up</h3>
    Signing up is a simple procedure, simply enter a valid username and password, and then click the Sign Up button<br  /><br  />
    For usernames, there are a few requirements.
    <ul>
        <li>The must be between 4 and 32 characters</li>
        <li>Only be made up of uppercase letters, lowercase letters and numbers, as well as underscores (_)</li>
        <li>The system will inform you if the username is already taken when you click away from the username entry box</li></ul>
    <br  />For passwords, they must be longer than 8 characters, and contain at least
    <ul>
        <li>1 Uppercase Letter
            <ul><li>e.g.&nbsp;&nbsp;&nbsp;A, E, Z, Y</li></ul></li>
        <li>1 Lowercase Letter
            <ul><li>e.g.&nbsp;&nbsp;&nbsp;z, a, n, e, f</li></ul></li>
        <li>1 Digit
            <ul><li>e.g.&nbsp;&nbsp;&nbsp;1, 5, 8, 2, 0</li></ul></li>
    </ul>

    <div class='centre'><B>Please do not use the same login details as your discord account, this is a seperate thing to discord.</b></div>

    <h3>Logging In</h3>
    After signing up, simply click on the login button to be redirected to the login page.<br  /><br  />
    This is designed similar to the sign-up page, simply enter the username and password you signed up with, and click the login button.<br  /><br  />
    Note: this doesn't check whether passwords or usernames are valid, so be careful what you enter.<br  /><br  />

    <h3>Logging Out</h3>
    Logging out is as simple as clicking the logout button, and then clicking on the link<br  /><br  />

    As cookiees are used as minimally as possible, only through <a href='https://www.w3schools.com/php/php_sessions.asp' rel='external' target='_blank'>PHP&#039;s Sessions</a>, the data cannot be stored indefinitely, so you will be automatically logged out after some time.

    <h2>Unit Search</h2>
    The Unit Search module allows you to easily search through all units, filtering them by a wide variety of criteria.

    <h3>Basic Search</h3>
    The basic search filters by only the unit's name, it's type, whether it is a special unit (not needing to be refunded on faction change) and the criteria for sorting the results.<br  /><br  />

    If the filters are left blank, they will not be considered in the search.

    Simply either enter a phrase you want to sort with (it doesn't need to be the whole name, and some common abbreviations like ISD II work), and select the options from the menus that you wish, and click the search button. It will search through all units, and find any that match the criteria.<br  />

    All of the units will be displayed in the same way as they are shown in the shop, with the same formatting.



    <h2>Further Reading</h2>
    Please also refer to the terms of service listed here: <a href='{$C('WEBSITE_ROOT')}/terms_of_use?r=$randstr'>Terms of Use</a><br  />
    For further documentation, and sourcing, please click here: <a target='_blank' rel='external' href='https://www.youtube.com/watch?v=dQw4w9WgXcQ'>Sourcing</a>
    </div>
    _END;

    echo "</div>";

    include_once DOCUMENT_ROOT . 'php/footer.php';