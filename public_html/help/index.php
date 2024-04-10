<?php
    require_once '../../php/functions.php';
    
    require_once DOCUMENT_ROOT . 'php/initialise.php';
    
    display_header($title, $randstr, $logged_in_as, $logged_in, $privilege);

    echo <<<_END
    <div class='centre-div'>
    <h1>User Documentation</h1>
    The following consists of all documentation for the modules of the software, please consult here if you have problems with using any module.<br  /><br  />

    <h2>General Navigation</h2>

    Navigation throughout the site is accomplished through the buttons below the header of the page. These link to all the installed and working modules, and allow you to access all you need.<br  /><br  />

    The links will change depending on whether you are logged in or not, and whether you have certain roles.<br  /><br  />

    There are also a few links down the bottom of the page, including a way to contact Me, and the terms of service<br  /><br  />

    <b>Please not, by using this website you agree to the terms of service, so please read them to understand what the rules around this are.</b><br  /><br  />

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

    As cookiees are used as minimally as possible, only through <a href='https://www.w3schools.com/php/php_sessions.asp' rel='external' target='_blank'>PHP&#039;s Sessions</a>, the data cannot be stored indefinitely, so you will be automatically logged out after some time.<br  /><br  />

    <h2>Unit Search</h2>
    The Unit Search module allows you to easily search through all units, filtering them by a wide variety of criteria.<br  /><br  />

    <h3>Basic Search</h3>
    The basic search filters by only the unit's name, it's type, whether it is a special unit (not needing to be refunded on faction change) and the criteria for sorting the results.<br  /><br  />

    If the filters are left blank, they will not be considered in the search.<br  /><br  />

    Simply either enter a phrase you want to sort with (it doesn't need to be the whole name, and some common abbreviations like ISD II work), and select the options from the menus that you wish, and click the search button. It will search through all units, and find any that match the criteria.<br  /><br  />

    All of the units will be displayed in the same way as they are shown in the shop, with the same formatting.<br  /><br  />

    If the sort setting is set blank, the units will be sorted by when they were originally entered into the database, from earliest to latest.<br  /><br  />

    Sadly, all filters are reset whenever you search, so you will need to readjust them as necessary<br  /><br  />

    <h3>Advanced Search</h3>

    There are a few extra options for filtering units, these are shown in the advanced search section.<br  /><br  />

    To show these options, simply click on the advanced search button.<br  /><br  />

    The advanced search function is mainly sliders, these indicate the accepted range of values for which you want for filter for each of the stats. If they are at the lowest or highest value, it will not filter by that stat. <br  /><br  />

    If you want to adjust the sliders in finer increments, you can use the box on either side to enter exact values. The search will include any values that are equal to the value given.<br  /><br  />

    If the unit does not have a defined number for the listed stat, unless the sliders are at the default values, it will not be shown.<br  /><br  />
    
    <h2>Shop Page</h2>

    the shop page has 2 options of display, the default one where it displays all shops, and one where it displays the units in a specific shop.<br  /><br  />

    <h3>All Shops</h3>
    Likely, if you have accessed the all shops page from the top bar of buttons, you will see a series of buttons for all the shops.<br  /><br  />

    Each button links to an individual page, showing all the units in each shop. To find out what is in a shop, simply click on the button and you will be sent to that page.<br  /><br  />

    <h3>Individual Shop Page</h3>

    You will get here if you click on a shop name link, either in a unit's page, or on the all shop page.<br  /><br  />

    This is a page that displays all the units in a shop, including their price, modslots, uc limit and any notes.<br  /><br  />

    <i>Please note that to avoid clutter, only the first 64 characters of the unit's notes are displayed, the rest can only be seen in it's unit page.</i><br  /><br  />

    If you want more stats about a unit, you can simply click on the name of the unit, which is a hyperlink and will redirect you to the unit's page.<br  /><br  />

    <h2>Unit Page</h2>

    Each unit has it's own page, individually generated from the stats of the unit recorded in the database.<br  /><br  />

    In this unit page, it includes stats about a ships armament, complement, crew numbers, what shops it's in, any notes and general statistics about the unit.<br  /><br  />

    If a stat of a unit is not displayed, it is not because it does not have that stat, it is because it does not have that stat in the database yet.<br  /><br  />

    If you find any errors with the stats of any unit, <a href='{$C('WEBSITE_ROOT')}/contact?r=$randstr'>contact me</a>.<br  /><br  />

    <h2>Modify Unit Stats</h2>

    <b>This is only accessible to people with the Shop Review Privilege.</b><br  /><br  />

    This is the modules that you use to update the statistics for any unit. Not all are completed, the ones that are have guides listed below.<br  /><br  />

    <h3>Add Shop</h3>

    This page has a simple text area for entering the shop's data, and a switch to designate whether it is a special shop or not.<br  /><br  />

    Simply copy paste the shops data, and click the button to submit it down the bottom. If units do not need to be refunded on faction change, ensure the 'special shop' slider is set to yes.

    This is quite fiddly with the stuff, so make sure of a few things.
    <ul>
    <li>All of the units are labeled under the correct name. Check on the confirm page that the unit names when clicked on redirect to the appropriate unit page.
    Capitalisation is important.</li>
    <li>The different types of units are all labelled under the correct section, like Vehicles, Capital Ships, or others.</li>
    <li>The first line of the shop text is the name of the shop.</li>
    </ul>

    The second page is only a confirm page, none of the stats here are final. Use this to check that all units that already have pages are linked to correctly, and all the units are named correctly with the correct stats.<br  /><br  />
    
    If they are not, click the no button to go back to the first page to fix them all.<br  /><br  />

    If everything is correct, click the yes button, and the units will be ingested into the shop. You will be redirected to the first page, with an option to enter another shop.<br  /><br  />

    <h3>Update Unit</h3>

    In a similar style, the update unit function takes either a wookieepedia link, or a unit stat post, and inserts them into the database. These must be accompanied with a relevant unit_id, and a unit_type for the ship, entered into the respective fields.<br  /><br  />

    You can use either form, as long as all values are entered. The site will either take the wookieepedia link and ingest the stats into a unit, or take the unit stat page and use that.

    This will most likely be not quite correct on the first try, as the unit has not been submitted to the database yet, you can comfortable scroll down and click the no button, so that you may go back to the original page.

    Some general rules that help ingesting units;
    <ul><li>Ensure that there is only one stat per line</li>
    <li>Ensure that there is a line above the complement, armament and crew that says exactly that</li>
    <li>If there is an ammo being detected, when there is not one, ensure that there is a series of spaces between any weapon types and numbers, so they are not accidently picked up.</li>
    <li>Make sure all stats have the correct name, ie exactly fire link, passengers, and more. Any misspellings will break the detection.</li>
    </ul>

    Once the unit is displaying correctly in the browser, simply click the yes button for it's stats to be sent to the user.<br  /><br  />

    <i>Note: there will be a distinct empty bracket pair at the top of the page, this is normal, and not a problem with the unit stats</i><br  /><br  />

    <h2>Further Reading</h2>
    Please also refer to the terms of service listed here: <a href='{$C('WEBSITE_ROOT')}/terms_of_use?r=$randstr'>Terms of Use</a><br  />
    For further documentation, and sourcing, please click here: <a target='_blank' rel='external' href='https://www.youtube.com/watch?v=dQw4w9WgXcQ'>Sourcing</a>
    </div>
    _END;

    echo "</div>";

    include_once DOCUMENT_ROOT . 'php/footer.php';