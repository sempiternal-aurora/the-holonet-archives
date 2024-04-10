function toggle_show_password() {
    var input = O('password_input_field');
    if (input.type === 'password') {
        input.type = 'text';
    } else {
        input.type = 'password';
    }
}