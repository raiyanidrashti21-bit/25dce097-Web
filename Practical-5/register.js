// ============================
// GET FORM ELEMENTS
// ============================

const form =
    document.getElementById("registrationForm");

const name =
    document.getElementById("name");

const email =
    document.getElementById("email");

const mobile =
    document.getElementById("mobile");

const password =
    document.getElementById("password");

const confirmPassword =
    document.getElementById("confirmPassword");

const course =
    document.getElementById("course");

const year =
    document.getElementById("year");

const terms =
    document.getElementById("terms");

const strengthBar =
    document.getElementById("strengthBar");

const strengthText =
    document.getElementById("strengthText");

const successMessage =
    document.getElementById("successMessage");


// ============================
// VALIDATE NAME
// ============================

function validateName() {

    const namePattern =
        /^[A-Za-z ]+$/;

    if (name.value.trim() === "") {

        document.getElementById("nameError").innerHTML =
            "Name is required.";

        name.classList.add("invalid");
        return false;

    }

    if (!namePattern.test(name.value)) {

        document.getElementById("nameError").innerHTML =
            "Name should contain only letters.";

        name.classList.add("invalid");
        return false;

    }

    document.getElementById("nameError").innerHTML = "";

    name.classList.remove("invalid");
    name.classList.add("valid");

    return true;
}


// ============================
// VALIDATE EMAIL
// ============================

function validateEmail() {

    const emailPattern =
        /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

    if (email.value.trim() === "") {

        document.getElementById("emailError").innerHTML =
            "Email is required.";

        email.classList.add("invalid");
        return false;

    }

    if (!emailPattern.test(email.value)) {

        document.getElementById("emailError").innerHTML =
            "Enter a valid email address.";

        email.classList.add("invalid");
        return false;

    }

    document.getElementById("emailError").innerHTML = "";

    email.classList.remove("invalid");
    email.classList.add("valid");

    return true;
}


// ============================
// VALIDATE MOBILE
// ============================

function validateMobile() {

    const mobilePattern =
        /^[0-9]{10}$/;

    if (mobile.value.trim() === "") {

        document.getElementById("mobileError").innerHTML =
            "Mobile number is required.";

        mobile.classList.add("invalid");
        return false;

    }

    if (!mobilePattern.test(mobile.value)) {

        document.getElementById("mobileError").innerHTML =
            "Mobile number must contain 10 digits.";

        mobile.classList.add("invalid");
        return false;

    }

    document.getElementById("mobileError").innerHTML = "";

    mobile.classList.remove("invalid");
    mobile.classList.add("valid");

    return true;
}


// ============================
// PASSWORD STRENGTH
// ============================

function checkPasswordStrength() {

    const value =
        password.value;

    let strength = 0;


    if (value.length >= 8) {

        strength++;

    }

    if (/[A-Z]/.test(value)) {

        strength++;

    }

    if (/[0-9]/.test(value)) {

        strength++;

    }

    if (/[!@#$%^&*]/.test(value)) {

        strength++;

    }


    if (value.length === 0) {

        strengthBar.style.width = "0%";

        strengthText.innerHTML =
            "Password strength";

    }

    else if (strength <= 1) {

        strengthBar.style.width = "25%";

        strengthText.innerHTML =
            "Weak password";

    }

    else if (strength <= 3) {

        strengthBar.style.width = "60%";

        strengthText.innerHTML =
            "Medium password";

    }

    else {

        strengthBar.style.width = "100%";

        strengthText.innerHTML =
            "Strong password";

    }

}


// ============================
// VALIDATE PASSWORD
// ============================

function validatePassword() {

    if (password.value === "") {

        document.getElementById("passwordError").innerHTML =
            "Password is required.";

        password.classList.add("invalid");
        return false;

    }

    if (password.value.length < 8) {

        document.getElementById("passwordError").innerHTML =
            "Password must contain at least 8 characters.";

        password.classList.add("invalid");
        return false;

    }

    document.getElementById("passwordError").innerHTML = "";

    password.classList.remove("invalid");
    password.classList.add("valid");

    return true;
}


// ============================
// CONFIRM PASSWORD
// ============================

function validateConfirmPassword() {

    if (confirmPassword.value === "") {

        document.getElementById("confirmPasswordError").innerHTML =
            "Please confirm your password.";

        confirmPassword.classList.add("invalid");
        return false;

    }

    if (confirmPassword.value !== password.value) {

        document.getElementById("confirmPasswordError").innerHTML =
            "Passwords do not match.";

        confirmPassword.classList.add("invalid");
        return false;

    }

    document.getElementById("confirmPasswordError").innerHTML = "";

    confirmPassword.classList.remove("invalid");
    confirmPassword.classList.add("valid");

    return true;
}


// ============================
// VALIDATE COURSE
// ============================

function validateCourse() {

    if (course.value === "") {

        document.getElementById("courseError").innerHTML =
            "Please select your course.";

        course.classList.add("invalid");
        return false;

    }

    document.getElementById("courseError").innerHTML = "";

    course.classList.remove("invalid");
    course.classList.add("valid");

    return true;
}


// ============================
// VALIDATE YEAR
// ============================

function validateYear() {

    if (year.value === "") {

        document.getElementById("yearError").innerHTML =
            "Please select your year.";

        year.classList.add("invalid");
        return false;

    }

    document.getElementById("yearError").innerHTML = "";

    year.classList.remove("invalid");
    year.classList.add("valid");

    return true;
}


// ============================
// VALIDATE GENDER
// ============================

function validateGender() {

    const gender =
        document.querySelector(
            'input[name="gender"]:checked'
        );


    if (!gender) {

        document.getElementById("genderError").innerHTML =
            "Please select your gender.";

        return false;

    }

    document.getElementById("genderError").innerHTML = "";

    return true;
}


// ============================
// VALIDATE TERMS
// ============================

function validateTerms() {

    if (!terms.checked) {

        document.getElementById("termsError").innerHTML =
            "You must accept the Terms and Conditions.";

        return false;

    }

    document.getElementById("termsError").innerHTML = "";

    return true;
}


// ============================
// REAL-TIME VALIDATION
// ============================

name.addEventListener("input", validateName);

email.addEventListener("input", validateEmail);

mobile.addEventListener("input", validateMobile);

password.addEventListener("input", function () {

    checkPasswordStrength();

    validatePassword();

});

confirmPassword.addEventListener(
    "input",
    validateConfirmPassword
);

course.addEventListener("change", validateCourse);

year.addEventListener("change", validateYear);


// ============================
// FORM SUBMIT
// ============================

form.addEventListener("submit", function (event) {

    event.preventDefault();


    const validName =
        validateName();

    const validEmail =
        validateEmail();

    const validMobile =
        validateMobile();

    const validPassword =
        validatePassword();

    const validConfirmPassword =
        validateConfirmPassword();

    const validCourse =
        validateCourse();

    const validYear =
        validateYear();

    const validGender =
        validateGender();

    const validTerms =
        validateTerms();


    if (
        validName &&
        validEmail &&
        validMobile &&
        validPassword &&
        validConfirmPassword &&
        validCourse &&
        validYear &&
        validGender &&
        validTerms
    ) {

        successMessage.innerHTML =
            "Registration successful!";

        form.reset();

        strengthBar.style.width = "0%";

        strengthText.innerHTML =
            "Password strength";

    }

    else {

        successMessage.innerHTML =
            "";

    }

});