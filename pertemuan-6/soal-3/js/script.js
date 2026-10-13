const screenReg = document.getElementById("screen-registration");
const screenEmail = document.getElementById("screen-email");
const screenSuccess = document.getElementById("screen-success");

const regForm = document.getElementById("regForm");
const emailForm = document.getElementById("emailForm");
const backLink = document.getElementById("backLink");

document.querySelectorAll('.error-msg').forEach(el => el.style.display = 'none');

regForm.addEventListener("submit", function (e) {
    e.preventDefault(); 

    const nameVal = document.getElementById("regName").value;
    const emailVal = document.getElementById("regEmail").value;
    const passVal = document.getElementById("regPassword").value;

    const nameErr = document.getElementById("name-error");
    const emailErr = document.getElementById("regEmail-error");
    const passErr = document.getElementById("password-error");

    let isValid = true;

    nameErr.style.display = "none";
    emailErr.style.display = "none";
    passErr.style.display = "none";

    const nameRegex = /^[a-zA-Z\s]{3,50}$/;
    if (!nameRegex.test(nameVal)) {
        nameErr.textContent = "Name must be 3-50 letters.";
        nameErr.style.display = "block";
        isValid = false;
    }

    const emailRegex = /^([a-zA-Z0-9._%+-]+)@([a-zA-Z0-9.-]+)\.([a-zA-Z]{2,})$/;
    if (!emailRegex.test(emailVal)) {
        emailErr.textContent = "Invalid email address.";
        emailErr.style.display = "block";
        isValid = false;
    }

    const passRegex = /^.{8,}$/;
    if (!passRegex.test(passVal)) {
        passErr.textContent = "Password must be at least 8 characters.";
        passErr.style.display = "block";
        isValid = false;
    }

    if (isValid) {
        document.getElementById("valEmail").value = emailVal; 

        screenReg.style.display = "none";
        screenEmail.style.display = "block";
    }
});

emailForm.addEventListener("submit", function (e) {
    e.preventDefault();

    const valEmailInput = document.getElementById("valEmail").value;
    const emailErrorMsg2 = document.getElementById("email-error");
    const emailPattern = /^([a-zA-Z0-9._%+-]+)@([a-zA-Z0-9.-]+)\.([a-zA-Z]{2,})$/;

    if (emailPattern.test(valEmailInput)) {
        emailErrorMsg2.style.display = "none"; 

        screenEmail.style.display = "none";
        screenSuccess.style.display = "block";
    } else {
        emailErrorMsg2.textContent = "Please enter a valid email address.";
        emailErrorMsg2.style.display = "block";
    }
});

backLink.addEventListener("click", function (e) {
    e.preventDefault();
    
    regForm.reset();
    emailForm.reset();
    document.querySelectorAll('.error-msg').forEach(el => el.style.display = 'none')
    screenSuccess.style.display = "none";
    screenReg.style.display = "block";
});

regForm.addEventListener("reset", function () {
    document.getElementById("name-error").style.display = "none";
    document.getElementById("regEmail-error").style.display = "none";
    document.getElementById("password-error").style.display = "none";
});