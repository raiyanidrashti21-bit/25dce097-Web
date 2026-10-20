
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student CRUD - Practical 13</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <div class="container">
        <h1>Student Management</h1>

        <p id="message" role="status" aria-live="polite"></p>

        <form id="studentForm">
            <input type="hidden" id="id">

            <label for="name">Name</label>
            <input type="text" id="name" required>

            <label for="email">Email</label>
            <input type="email" id="email" required>

            <label for="mobile">Mobile</label>
            <input type="text" id="mobile"
                   inputmode="numeric" pattern="[0-9]{10}"
                   maxlength="10" required>

            <label for="course">Course</label>
            <input type="text" id="course" required>

            <button type="submit" id="saveButton">Add Student</button>
            <button type="button" id="cancelButton">Cancel Edit</button>
        </form>

        <h2>Student Records</h2>
        <p id="loading" hidden>Loading students...</p>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Mobile</th>
                        <th>Course</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="studentList"></tbody>
            </table>
        </div>
    </div>

    <script src="script.js"></script>
</body>
</html>
