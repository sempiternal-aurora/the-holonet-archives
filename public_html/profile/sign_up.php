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

    $error = $user = $pass = '';
    if (isset($_SESSION['user'])) destroy_session_completely();

    if (isset($_POST['user'])) {
        $user = sanitise_string($pdo, $_POST['user']);
        $pass = sanitise_string($pdo, $_POST['pass']);

        if ($user == '' || $pass == '') $error = 'Not all fields were entered<br  /><br  />';
        else {
            $result = $pdo->query("SELECT * FROM user WHERE username='$user'");

            $is_valid_user = validate_username($user);
            $is_valid_pass = validate_password($pass);

            if ($result->rowCount()) $error = 'That username already exists<br  /><br  />';
            elseif ($is_valid_user != '') $error .= $is_valid_user;
            elseif ($is_valid_pass != '') $error .= $is_valid_pass;
            else {
                $hash = password_hash($pass, PASSWORD_DEFAULT);
                $result = $pdo->query("INSERT INTO user VALUES ('$user', '$hash')");
                die('<h4>Account created</h4>Please Log in.</div></body></html>');
            }
        }
    }

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

    include_once DOCUMENT_ROOT . '/php/footer.php';