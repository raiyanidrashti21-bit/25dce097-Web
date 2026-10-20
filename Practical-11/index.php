
<?php
require_once "db.php";

function clean($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, "UTF-8");
}

$message = "";
$error = "";
$editStudent = null;

// ADD STUDENT
if ($_SERVER["REQUEST_METHOD"] === "POST" &&
    ($_POST["action"] ?? "") === "add") {

    $name = trim($_POST["name"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $course = trim($_POST["course"] ?? "");
    $mobile = trim($_POST["mobile"] ?? "");

    if ($name === "" || !filter_var($email, FILTER_VALIDATE_EMAIL)
        || $course === "") {
        $error = "Enter a valid name, email, and course.";
    } else {
        $stmt = $conn->prepare(
            "INSERT INTO students (name, email, course, mobile)
             VALUES (?, ?, ?, ?)"
        );
        $stmt->bind_param("ssss", $name, $email, $course, $mobile);

        if ($stmt->execute()) {
            $message = "Student added successfully.";
        } elseif ($stmt->errno == 1062) {
            $error = "This email already exists.";
        } else {
            $error = "Failed to add student.";
        }
        $stmt->close();
    }
}

// UPDATE STUDENT
if ($_SERVER["REQUEST_METHOD"] === "POST" &&
    ($_POST["action"] ?? "") === "update") {

    $id = filter_var($_POST["id"] ?? "", FILTER_VALIDATE_INT);
    $name = trim($_POST["name"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $course = trim($_POST["course"] ?? "");
    $mobile = trim($_POST["mobile"] ?? "");

    if (!$id || $name === "" ||
        !filter_var($email, FILTER_VALIDATE_EMAIL) || $course === "") {
        $error = "Enter valid student details.";
    } else {
        $stmt = $conn->prepare(
            "UPDATE students
             SET name=?, email=?, course=?, mobile=?
             WHERE id=?"
        );
        $stmt->bind_param("ssssi", $name, $email, $course, $mobile, $id);

        if ($stmt->execute()) {
            if ($stmt->affected_rows > 0) {
                $message = "Student updated successfully.";
            } else {
                $check = $conn->prepare("SELECT id FROM students WHERE id=?");
                $check->bind_param("i", $id);
                $check->execute();
                $check->store_result();

                if ($check->num_rows > 0) {
                    $message = "No changes were made.";
                } else {
                    $error = "Student not found.";
                }
                $check->close();
            }
        } elseif ($stmt->errno == 1062) {
            $error = "Another student already uses this email.";
        } else {
            $error = "Failed to update student.";
        }
        $stmt->close();
    }
}

// DELETE STUDENT
if ($_SERVER["REQUEST_METHOD"] === "POST" &&
    ($_POST["action"] ?? "") === "delete") {

    $id = filter_var($_POST["id"] ?? "", FILTER_VALIDATE_INT);

    if (!$id) {
        $error = "Invalid student ID.";
    } else {
        $stmt = $conn->prepare("DELETE FROM students WHERE id=?");
        $stmt->bind_param("i", $id);

        if ($stmt->execute() && $stmt->affected_rows > 0) {
            $message = "Student deleted successfully.";
        } else {
            $error = "Student not found or deletion failed.";
        }
        $stmt->close();
    }
}

// SEARCH AND FILTER
$search = trim($_GET["search"] ?? "");
$courseFilter = trim($_GET["course"] ?? "");
$page = max(1, (int)($_GET["page"] ?? 1));
$limit = 5;

$like = "%" . $search . "%";

// COUNT MATCHING STUDENTS
$countStmt = $conn->prepare(
    "SELECT COUNT(*) FROM students
     WHERE (? = '' OR name LIKE ? OR email LIKE ? OR course LIKE ?)
     AND (? = '' OR course = ?)"
);

$countStmt->bind_param(
    "ssssss",
    $search,
    $like,
    $like,
    $like,
    $courseFilter,
    $courseFilter
);

$countStmt->execute();
$countStmt->bind_result($total);
$countStmt->fetch();
$countStmt->close();

$totalPages = max(1, (int)ceil($total / $limit));
$page = min($page, $totalPages);
$offset = ($page - 1) * $limit;

// FETCH STUDENTS IN ALPHABETICAL ORDER
$stmt = $conn->prepare(
    "SELECT id, name, email, course, mobile
     FROM students
     WHERE (? = '' OR name LIKE ? OR email LIKE ? OR course LIKE ?)
     AND (? = '' OR course = ?)
     ORDER BY id ASC  LIMIT ? OFFSET ?"
);

$stmt->bind_param(
    "ssssssii",
    $search,
    $like,
    $like,
    $like,
    $courseFilter,
    $courseFilter,
    $limit,
    $offset
);

$stmt->execute();
$result = $stmt->get_result();

// COURSE OPTIONS
$courses = $conn->query(
    "SELECT DISTINCT course FROM students
     WHERE course <> ''
     ORDER BY course ASC"
);

// LOAD STUDENT FOR EDITING
if (isset($_GET["edit"])) {
    $editId = filter_var($_GET["edit"], FILTER_VALIDATE_INT);

    if ($editId) {
        $editStmt = $conn->prepare(
            "SELECT id, name, email, course, mobile
             FROM students WHERE id=?"
        );
        $editStmt->bind_param("i", $editId);
        $editStmt->execute();

        $editStudent = $editStmt->get_result()->fetch_assoc();
        $editStmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Student Management System</title>

<style>
body {
    font-family: Arial, sans-serif;
    background: #f3f6fb;
    margin: 0;
    padding: 25px;
    color: #243047;
}
.container {
    max-width: 1100px;
    margin: auto;
}
h1, h2 {
    color: #234b83;
}
.card {
    background: white;
    padding: 20px;
    margin-bottom: 20px;
    border-radius: 10px;
    box-shadow: 0 2px 8px #00000012;
}
input, select, button, .btn {
    padding: 10px;
    margin: 5px 3px;
    border: 1px solid #ccd4df;
    border-radius: 5px;
    box-sizing: border-box;
}
button, .btn {
    background: #315e9e;
    color: white;
    cursor: pointer;
    text-decoration: none;
    display: inline-block;
}
.delete {
    background: #bd3545;
}
.cancel {
    background: #65758b;
}
.success {
    background: #d9f5e2;
    color: #176538;
    padding: 12px;
}
.error {
    background: #ffe1e1;
    color: #9d2020;
    padding: 12px;
}
.table-wrap {
    overflow-x: auto;
}
table {
    width: 100%;
    border-collapse: collapse;
    background: white;
}
th, td {
    padding: 11px;
    text-align: left;
    border-bottom: 1px solid #e0e5ed;
}
th {
    background: #e8eff9;
}
.actions {
    white-space: nowrap;
}
.pagination {
    margin-top: 15px;
}
@media (max-width: 600px) {
    body {
        padding: 10px;
    }
    .card {
        padding: 12px;
    }
}
</style>
</head>

<body>
<div class="container">

<h1>Student Management System</h1>
<p>Add, view, edit, delete, search, and filter student records.</p>

<?php if ($message !== ""): ?>
    <p class="success"><?= clean($message) ?></p>
<?php endif; ?>

<?php if ($error !== ""): ?>
    <p class="error"><?= clean($error) ?></p>
<?php endif; ?>

<div class="card">
<h2><?= $editStudent ? "Edit Student" : "Add New Student" ?></h2>

<form method="post" action="index.php">
    <input type="hidden" name="action"
           value="<?= $editStudent ? "update" : "add" ?>">

    <?php if ($editStudent): ?>
        <input type="hidden" name="id"
               value="<?= (int)$editStudent["id"] ?>">
    <?php endif; ?>

    <input type="text" name="name" placeholder="Student name"
           maxlength="100" required
           value="<?= clean($editStudent["name"] ?? "") ?>">

    <input type="email" name="email" placeholder="Email address"
           maxlength="100" required
           value="<?= clean($editStudent["email"] ?? "") ?>">

    <input type="text" name="course" placeholder="Course"
           maxlength="100" required
           value="<?= clean($editStudent["course"] ?? "") ?>">

    <input type="text" name="mobile" placeholder="Mobile number"
           maxlength="15"
           value="<?= clean($editStudent["mobile"] ?? "") ?>">

    <button type="submit">
        <?= $editStudent ? "Update Student" : "Add Student" ?>
    </button>

    <?php if ($editStudent): ?>
        <a class="btn cancel" href="index.php">Cancel</a>
    <?php endif; ?>
</form>
</div>

<div class="card">
<h2>Search and Filter Students</h2>

<form method="get" action="index.php">
    <input type="text" name="search"
           placeholder="Search name, email, or course"
           value="<?= clean($search) ?>">

    <select name="course">
        <option value="">All Courses</option>

        <?php while ($c = $courses->fetch_assoc()): ?>
            <option value="<?= clean($c["course"]) ?>"
                <?= $courseFilter === $c["course"] ? "selected" : "" ?>>
                <?= clean($c["course"]) ?>
            </option>
        <?php endwhile; ?>
    </select>

    <button type="submit">Search / Filter</button>
    <a class="btn cancel" href="index.php">Reset</a>
</form>
</div>

<div class="card">
<h2>Student Records (<?= (int)$total ?> found)</h2>

<div class="table-wrap">
<table>
<thead>
<tr>
    <th>ID</th>
    <th>Name</th>
    <th>Email</th>
    <th>Course</th>
    <th>Mobile</th>
    <th>Actions</th>
</tr>
</thead>
<tbody>

<?php if ($result->num_rows > 0): ?>
    <?php while ($student = $result->fetch_assoc()): ?>
        <tr>
            <td><?= (int)$student["id"] ?></td>
            <td><?= clean($student["name"]) ?></td>
            <td><?= clean($student["email"]) ?></td>
            <td><?= clean($student["course"]) ?></td>
            <td><?= clean($student["mobile"]) ?></td>
            <td class="actions">
                <a class="btn"
                   href="index.php?edit=<?= (int)$student["id"] ?>">
                   Edit
                </a>

                <form method="post" action="index.php"
                      style="display:inline"
                      onsubmit="return confirm('Are you sure you want to delete this student?');">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id"
                           value="<?= (int)$student["id"] ?>">
                    <button type="submit" class="delete">Delete</button>
                </form>
            </td>
        </tr>
    <?php endwhile; ?>
<?php else: ?>
    <tr>
        <td colspan="6">No student records found.</td>
    </tr>
<?php endif; ?>

</tbody>
</table>
</div>

<div class="pagination">
    <?php if ($page > 1): ?>
        <a class="btn"
           href="?search=<?= urlencode($search) ?>&course=<?= urlencode($courseFilter) ?>&page=<?= $page - 1 ?>">
           Previous
        </a>
    <?php endif; ?>

    Page <?= $page ?> of <?= $totalPages ?>

    <?php if ($page < $totalPages): ?>
        <a class="btn"
           href="?search=<?= urlencode($search) ?>&course=<?= urlencode($courseFilter) ?>&page=<?= $page + 1 ?>">
           Next
        </a>
    <?php endif; ?>
</div>
</div>
</div>

</body>
</html>

<?php
$stmt->close();
$conn->close();
?>
