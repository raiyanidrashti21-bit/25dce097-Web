<?php
$conn = mysqli_connect("localhost", "root", "", "practical10");

if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}
?>
