```php
<!DOCTYPE html>
<html>
<head>
    <title>StudentHub Registration</title>
</head>

<body>

    <h2>StudentHub Registration</h2>

    <form method="POST" action="process.php">

        <label>Name:</label><br>
        <input type="text" name="name">
        <br><br>

        <label>Email:</label><br>
        <input type="email" name="email">
        <br><br>

        <label>Mobile:</label><br>
        <input type="text" name="mobile">
        <br><br>

        <label>Course:</label><br>
        <select name="course">
            <option value="">Select Course</option>
            <option value="Computer Engineering">Computer Engineering</option>
            <option value="Computer Science">Computer Science</option>
            <option value="Information Technology">Information Technology</option>
        </select>
        <br><br>

        <button type="submit">Register</button>

    </form>

</body>
</html>
```
