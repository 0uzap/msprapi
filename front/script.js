function togglePassword(id) {
    const passwordInput = document.getElementById(id);
    if (passwordInput.type === "password") {
        passwordInput.type = "text";
    } else {
        passwordInput.type = "password";
    }
}

function checkPasswords() {
    const pw1 = document.getElementById("password").value;
    const pw2 = document.getElementById("confirm_password").value;
    if (pw1 !== pw2) {
        alert("Les mots de passe ne correspondent pas.");
        return false;
    }
    return true;
}
