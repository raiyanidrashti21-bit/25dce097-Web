<?php
$conn = mysqli_connect("localhost", "root", "", "practical9");

if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}
?>