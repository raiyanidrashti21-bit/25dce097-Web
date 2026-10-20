
<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Get and sanitize form data
    $name = trim($_POST["name"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $mobile = trim($_POST["mobile"] ?? "");
    $course = trim($_POST["course"] ?? "");

    $name = htmlspecialchars($name, ENT_QUOTES, "UTF-8");
    $course = htmlspecialchars($course, ENT_QUOTES, "UTF-8");

    // Server-side validation
    if (
        $name == "" ||
        !filter_var($email, FILTER_VALIDATE_EMAIL) ||
        !preg_match("/^[0-9]{10}$/", $mobile) ||
        $course == ""
    ) {
        die("Error: Please enter valid registration details.");
    }

    // Store data in CSV file
    $file = fopen("students.csv", "a");

    if ($file === false) {
        die("Error: Unable to open the file.");
    }

    if (fputcsv($file, [$name, $email, $mobile, $course]) === false) {
        fclose($file);
        die("Error: Unable to save registration.");
    }

    fclose($file);

    echo "<h2>Registration Successful!</h2>";
    echo "<p>Name: " . $name . "</p>";
    echo "<p>Email: " .
         htmlspecialchars($email, ENT_QUOTES, "UTF-8") . "</p>";
    echo "<p>Mobile: " .
         htmlspecialchars($mobile, ENT_QUOTES, "UTF-8") . "</p>";
    echo "<p>Course: " . $course . "</p>";

} else {
    echo "Error: Please submit the form first.";
}
?>