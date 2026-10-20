
<!DOCTYPE html>
<html>
<head>
    <title>StudentHub - Student Registration</title>
</head>
<body>

    <h2>Student Registration Form</h2>

    <form action="process.php" method="POST">

        <label for="name">Student Name:</label>
        <input type="text" id="name" name="name" required>
        <br><br>

        <label for="email">Email ID:</label>
        <input type="email" id="email" name="email" required>
        <br><br>

        <label for="mobile">Mobile Number:</label>
        <input type="text" id="mobile" name="mobile"
               maxlength="10" required>
        <br><br>

        <label for="course">Course:</label>
        <input type="text" id="course" name="course" required>
        <br><br>

        <button type="submit">Register</button>

    </form>

</body>
</html>