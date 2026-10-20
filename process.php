<?php

require "db.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $name = $_POST["name"] ?? "";
    $email = $_POST["email"] ?? "";
    $mobile = $_POST["mobile"] ?? "";
    $course = $_POST["course"] ?? "";

    $name = trim($name);
    $email = trim($email);
    $mobile = trim($mobile);
    $course = trim($course);

    if ($name == "" || $email == "" || $mobile == "" || $course == "") {

        echo "<h3>Please fill all the fields.</h3>";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        echo "<h3>Please enter a valid email address.</h3>";

    } elseif (!preg_match("/^[0-9]{10}$/", $mobile)) {

        echo "<h3>Mobile number must contain exactly 10 digits.</h3>";

    } else {

        try {

            $sql = "INSERT INTO students (name, email, mobile, course)
                    VALUES (:name, :email, :mobile, :course)";

            $stmt = $conn->prepare($sql);

            $stmt->execute([
                ":name" => $name,
                ":email" => $email,
                ":mobile" => $mobile,
                ":course" => $course
            ]);

            echo "<h3>Registration submitted successfully!</h3>";

        } catch (PDOException $e) {

            echo "<h3>Unable to save the registration.</h3>";
        }
    }

} else {

    echo "<h3>Invalid request.</h3>";
}

?>