<?php
require_once "db.php";

$errors = [];
$success = "";
$name = $username = $email = $mobile = $course = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Collect input
    $name     = trim($_POST["name"] ?? "");
    $username = trim($_POST["username"] ?? "");
    $email    = trim($_POST["email"] ?? "");
    $mobile   = trim($_POST["mobile"] ?? "");
    $course   = trim($_POST["course"] ?? "");
    $password = $_POST["password"] ?? "";
    $confirm  = $_POST["confirm_password"] ?? "";

    // Backend validation (same rules as the JavaScript)
    if (!preg_match("/^[A-Za-z ]{3,100}$/", $name)) {
        $errors[] = "Name must be 3-100 letters and spaces only.";
    }
    if (!preg_match("/^[A-Za-z0-9_]{4,20}$/", $username)) {
        $errors[] = "Username must be 4-20 characters: letters, numbers, underscore.";
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Enter a valid email address.";
    }
    if (!preg_match("/^[0-9]{10}$/", $mobile)) {
        $errors[] = "Mobile number must be exactly 10 digits.";
    }
    if ($course === "") {
        $errors[] = "Course is required.";
    }
    if (!preg_match("/^(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9]).{8,}$/", $password)) {
        $errors[] = "Password must be 8+ characters with uppercase, lowercase and a number.";
    }
    if ($password !== $confirm) {
        $errors[] = "Passwords do not match.";
    }

    // Duplicate checks (only if input is valid so far)
    if (empty($errors)) {
        $stmt = mysqli_prepare($conn, "SELECT id FROM students WHERE email = ?");
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);
        if (mysqli_stmt_num_rows($stmt) > 0) {
            $errors[] = "This email is already registered.";
        }
        mysqli_stmt_close($stmt);

        $stmt = mysqli_prepare($conn, "SELECT id FROM students WHERE username = ?");
        mysqli_stmt_bind_param($stmt, "s", $username);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);
        if (mysqli_stmt_num_rows($stmt) > 0) {
            $errors[] = "This username is already taken.";
        }
        mysqli_stmt_close($stmt);
    }

    // Hash password and insert
    if (empty($errors)) {
        $hashed = password_hash($password, PASSWORD_DEFAULT);

        try {
            $stmt = mysqli_prepare($conn,
                "INSERT INTO students (name, username, email, mobile, course, password)
                 VALUES (?, ?, ?, ?, ?, ?)");
            mysqli_stmt_bind_param($stmt, "ssssss",
                $name, $username, $email, $mobile, $course, $hashed);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);

            $success = "Registration successful!";
            $name = $username = $email = $mobile = $course = "";
        } catch (mysqli_sql_exception $e) {
            $errors[] = "Registration failed. Please try again.";
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>User Registration</title>
        <style>
        body { font-family: Arial, sans-serif; background: #f2f2f2; }
        .box { width: 400px; margin: 30px auto; background: #fff; padding: 20px; border-radius: 6px; }
        input, select { width: 100%; padding: 8px; margin: 5px 0 12px; box-sizing: border-box; }
        button { padding: 10px 20px; }
        .error { color: #b00020; background: #fde7ea; padding: 8px; margin-bottom: 10px; }
        .success { color: #0a6b2d; background: #e3f6e9; padding: 8px; margin-bottom: 10px; }
        #jsError { color: #b00020; margin-bottom: 10px; }
    </style>
</head>
<body>
<div class="box">
    <h2>User Registration</h2>

    <?php if ($success): ?>
        <div class="success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <?php foreach ($errors as $err): ?>
        <div class="error"><?= htmlspecialchars($err) ?></div>
    <?php endforeach; ?>

    <div id="jsError"></div>

    <form method="post" action="" id="regForm" onsubmit="return validateForm()" novalidate>
        <label>Name</label>
        <input type="text" name="name" id="name" value="<?= htmlspecialchars($name) ?>">

        <label>Username</label>
        <input type="text" name="username" id="username" value="<?= htmlspecialchars($username) ?>">

        <label>Email</label>
        <input type="email" name="email" id="email" value="<?= htmlspecialchars($email) ?>">

        <label>Mobile</label>
        <input type="text" name="mobile" id="mobile" value="<?= htmlspecialchars($mobile) ?>">

        <label>Course</label>
        <select name="course" id="course">
            <option value="">-- Select --</option>
            <?php foreach (["BCA", "BSc IT", "MCA", "MSc IT"] as $c): ?>
                <option value="<?= $c ?>" <?= $course === $c ? "selected" : "" ?>><?= $c ?></option>
            <?php endforeach; ?>
        </select>

        <label>Password</label>
        <input type="password" name="password" id="password">

        <label>Confirm Password</label>
        <input type="password" name="confirm_password" id="confirm_password">

        <button type="submit">Register</button>
    </form>
</div>

<script>
function validateForm() {
    var name = document.getElementById("name").value.trim();
    var username = document.getElementById("username").value.trim();
    var email = document.getElementById("email").value.trim();
    var mobile = document.getElementById("mobile").value.trim();
    var course = document.getElementById("course").value;
    var password = document.getElementById("password").value;
    var confirmPw = document.getElementById("confirm_password").value;
    var errors = [];

    if (!/^[A-Za-z ]{3,100}$/.test(name))
        errors.push("Name must be 3-100 letters and spaces only.");
    if (!/^[A-Za-z0-9_]{4,20}$/.test(username))
        errors.push("Username must be 4-20 characters: letters, numbers, underscore.");
    if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email))
        errors.push("Enter a valid email address.");
    if (!/^[0-9]{10}$/.test(mobile))
        errors.push("Mobile number must be exactly 10 digits.");
    if (course === "")
        errors.push("Course is required.");
    if (!/^(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9]).{8,}$/.test(password))
        errors.push("Password must be 8+ characters with uppercase, lowercase and a number.");
    if (password !== confirmPw)
        errors.push("Passwords do not match.");

    document.getElementById("jsError").innerHTML = errors.join("<br>");
    return errors.length === 0;
}
</script>
</body>
</html>