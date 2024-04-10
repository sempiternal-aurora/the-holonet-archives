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
                $('#show_password').on('click', function() {
                    var passwordField = $('#password_input_field');
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

            if ($result->rowCount()) $error = 'That username already exists<br  /><br  />';
            else {
                $result = $pdo->query("INSERT INTO members VALUES ('$user', '$pass')");
                die('<h4>Account created</h4>Please Log in.</div></body></html>');
            }
        }
    }

    echo <<<_END
            <form method='post' action='signup.php?r=$randstr'>$error
            <div>
                <div class='login_form'>
                    <div>Please enter your details to sign up</div>
                    <div class='ui-field-contain'>
                        <label for='user'>Username</label>
                        <input type='text' maxlength='32' name='user' value='$user' onBlur='check_user(this)'  />
                    </div>
                    <div id='used'>&nbsp;</div>
                    <div class='ui-field-contain'>
                        <label for='pass'>Password</label>
                        <input type='password' maxlength='32' name='pass' value='$pass' id='password_input_field'  />
                    </div>
                    <div class='ui-field-contain'>
                        <label></label>
                        <button type='button' class='ui-btn ui-btn-inline' id='show_password'>Show</button>
                    </div>
                    <div class='ui-field-contain'>
                        <label></label>
                        <input class='ui-btn ui-btn-inline' data-transition='slide' type='submit' value='Sign Up'  />
                    </div>
                </div>
            </div>
            </form>
        </div>
    _END;

    include_once DOCUMENT_ROOT . '/php/footer.php';