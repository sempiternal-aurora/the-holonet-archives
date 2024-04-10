function validate_username(field) {
    if (field == "") return "No Username was entered.\n";
    else if (field.length < 4) return "Usernames must be at least 4 characters.\n";
    else if (field.length > 32) return "Usernames must be no longer than 32 characters.\n";
    else if (/[^a-zA-Z0-9_]/.test(field)) return "Only a-z, A-Z, 0-9 and _ allowed in Usernames.\n";
    else return "";
}

function validate_password(field) {
    if (field == "") return "No Password was entered.\n";
    else if (field.length < 8) return "Passwords must be at least 8 characters\n";
    else if (!/[a-z]/.test(field) || !/[A-Z]/.test(field) || !/[0-9]/.test(field)) {
        return "Passwords require one each of a-z, A-Z and 0-9,\n";
    }
    else return "";
}

function validate_login(form) {
    fail = validate_username(form.user.value)
    fail += validate_password(form.pass.value)

    if (fail == "") return true
    else { $('.error').text(fail); return false }
}

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


