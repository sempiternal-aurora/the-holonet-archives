function validateUsername(field) {
    if (field == "") return "No Username was entered.\n";
    else if (field.length < 4) return "Usernames must be at least 4 characters.\n";
    else if (/[^a-zA-Z0-9_]/.test(field)) return "Only a-z, A-Z, 0-9 and _ allowed in Usernames.\n";
    else return "";
}

function validatePassword(field) {
    if (field == "") return "No Password was entered.\n";
    else if (field.length < 8) return "Passwords must be at least 8 characters\n";
    else if (!/[a-z]/.test(field) || !/[A-Z]/.test(field) || !/[0-9]/.test(field)) {
        return "Passwords require one each of a-z, A-Z and 0-9,\n";
    }
    else return "";
}

function validate(form) {
    fail = validateUsername(form.user.value)
    fail += validatePassword(form.pass.value)

    if (fail == "") return true
    else { alert(fail); return false }
}