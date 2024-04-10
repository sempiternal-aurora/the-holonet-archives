<?php
    require_once '../../php/functions.php';
    require_once DOCUMENT_ROOT . 'php/header.php';

    echo <<<_END
        <script type='text/javascript'>
            function check_user(user) {
                if (user.value == '') {
                    $('#used').html('&nbsp;')
                    return
                }
            
                $.post(
                    'checkuser.php', 
                    { user : user.value }, 
                    function(data) {
                        $('#used').html(data)
                    }
                )
            }

            $(document).ready(function(){
                $('#show-password').on('click', function() {
                    var passwordField = $('#password-input-field');
                    var passwordFieldType = passwordField.attr('type');
                    if (passwordFieldType == 'password') {
                        passwordField.attr('type', 'text')
                        $(this).text('Hide')
                    } else {
                        passwordField.attr('type', 'password')
                        $(this).text('Show')
                    }
                });
            });
        </script>

    _END;

    $error = $user = $pass = ''; //initialise variables
    if (isset($_SESSION['user'])) destroy_session_completely(); //if user is already logged in, lot them out

    if (isset($_POST['user'])) { //if the user has already filled in the form and submitted it
        $user = sanitise_string($pdo, $_POST['user']); //sanitise the imputs they have supplied
        $pass = sanitise_string($pdo, $_POST['pass']);

        if ($user == '' || $pass == '') $error = 'Not all fields were entered<br  /><br  />'; //if the fields are blank, return an error message
        else {
            $result = $pdo->query("SELECT username, pass FROM user WHERE username='$user'"); //check for users with the username the user gave us

            $is_valid_user = validate_username($user); //check if the username given is valid
            $is_valid_pass = validate_password($pass); //check if the password given is valid

            if ($result->rowCount()) $error = 'That username already exists<br  /><br  />'; //if there exists a record for the username given, the username is taken, and inform the user for them to try again
            elseif ($is_valid_user != '') $error .= $is_valid_user; //if the validate function found a problem with either username or password, these problems are
            elseif ($is_valid_pass != '') $error .= $is_valid_pass; //appended to the error message shown to the user
            else { //otherwise, if the username is taken and it and the password are valid
                $hash = password_hash($pass, PASSWORD_DEFAULT); //hash the password for security
                $result = $pdo->query("INSERT INTO user VALUES ('$user', '$hash')"); //and insert it into the database of users for future reference
                die('<h4>Account created</h4>Please Log in.</div></body></html>'); //inform the user that their account has been created, and ask them to login, stopping the script
            }
        }
    }
    /*
        What follows is the form sent to the user that gathers password and username and calls this script with the values they give. Also includes basic validation to save server resources.
    */

    echo <<<_END
            <form method='post' action='sign_up.php?r=$randstr' onsubmit='return validate(this)'>$error
            <div class='login-form'>
                <div class='centre'>Please enter your details to sign up</div>
                <div class='top-margin'>    
                    <label for='user'><span class='form-label'>Username</span></label>
                    <div>
                        <input type='text' maxlength='32' name='user' value='$user' id='username-field' onBlur='check_user(this)'  />
                    </div>
                    <div id='used'>&nbsp;</div>
                </div>
                <label for='pass'><span class='form-label'>Password</span></label>
                <div class='ui-field-contain' id='pass-div-cont'>
                    <input type='password' maxlength='32' name='pass' value='$pass' id='password-input-field' autocomplete='off'  />
                    <button type='button' class='ui-btn ui-btn-inline btn-thin' id='show-password'>Show</button>
                </div>
                <div class='ui-field-contain'>
                    <label for='sign-up-btn' class='ui-hidden-accessible'>Sign Up</label>
                    <input class='submit-btn ui-btn ui-btn-inline' id='sign-up-btn' data-transition='slide' type='submit' value='Sign Up'  />
                </div>
            </div>
            </form>
        </div>
    _END;

    include_once DOCUMENT_ROOT . '/php/footer.php'; //footer