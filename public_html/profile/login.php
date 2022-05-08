<?php
    require_once '../../php/functions.php';
    require_once DOCUMENT_ROOT . 'php/header.php';

    $error = $user = $pass = ''; //initialise variable
    $pdo = initialise_mysql_connection(MYSQL_HOST, MYSQL_DATABASE, MYSQL_USER, MYSQL_PASS);

    if (isset($_POST['user'])) {
        $user = sanitise_string($pdo, $_POST['user']);
        $pass = sanitise_string($pdo, $_POST['pass']);

        if ($user == '' || $pass == '') {
            $error = 'Not all fields were entered';
        } else {
            $result = $pdo->query("SELECT username, pass FROM user WHERE username='$user'");

            if ($result->rowCount() == 0) {
                $error = 'Username or Password Incorrect';
            } else {
                $row = $result->fetch();
                $hash = $row['pass'];

                if (password_verify($pass, $hash)) {
                    $_SESSION['user'] = $user;
                    $_SESSION['pass'] = $hash;
                    $logged_in = TRUE;
                } else {
                    $error = 'Username or Password Incorrect';
                }
            }
        }
    }

    if (isset($_SESSION['user'])) {
        echo <<<_END
        <div class='centre'>You have been logged in successfully.
        <a data-transition='slide' href='{$C('WEBSITE_ROOT')}/profile?r=$randstr'>Click here</a>
        </div></div>
        _END;
    } else {
        echo <<<_END
        <form method='post' action='login.php?r=$randstr'>
        <div class='login-form'>
            <div class='ui-field-contain'>
                <label class='ui-hidden-accessible' for='error-div'>Error</label>
                <span class='error' id='error-div'>$error</span>
            </div>
            <div class='top-margin'>    
                <label for='user'><span class='form-label'>Username</span></label>
                <div>
                    <input type='text' maxlength='32' name='user' value='$user' id='username-field'  />
                </div>
            </div>
            <div class='top-margin'><label for='pass'><span class='form-label'>Password</span></label></div>
            <input type='password' maxlength='32' name='pass' value='' data-clear-btn='true' id='password-input-field' autocomplete='off'  />
                <label for='login-btn' class='ui-hidden-accessible'>login</label>
            <input id='login-btn' data-transition='slide' type='submit' value='Login'  />
        </div>
        </form>
        </div>
        _END;
    }

    include_once DOCUMENT_ROOT . '/php/footer.php';

    
    