
<?php
header("Content-Type: application/json; charset=UTF-8");

require "db.php";

function respond($success, $message, $data = null) {
    echo json_encode([
        "success" => $success,
        "message" => $message,
        "data" => $data
    ]);
    exit;
}

$method = $_SERVER["REQUEST_METHOD"];

if ($method === "GET") {
    $result = $conn->query(
        "SELECT id, name, email, mobile, course FROM students ORDER BY id"
    );

    if (!$result) {
        http_response_code(500);
        respond(false, "Unable to fetch students");
    }

    $students = [];

    while ($row = $result->fetch_assoc()) {
        $students[] = $row;
    }

    respond(true, "Students fetched successfully", $students);
}

if ($method === "POST") {
    $input = json_decode(file_get_contents("php://input"), true);

    if (!is_array($input)) {
        http_response_code(400);
        respond(false, "Invalid JSON data");
    }

    $action = $input["action"] ?? "";

    if ($action === "add" || $action === "update") {
        $name = trim($input["name"] ?? "");
        $email = trim($input["email"] ?? "");
        $mobile = trim($input["mobile"] ?? "");
        $course = trim($input["course"] ?? "");

        if ($name === "" || $email === "" ||
            $mobile === "" || $course === "") {
            http_response_code(400);
            respond(false, "All fields are required");
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            http_response_code(400);
            respond(false, "Enter a valid email address");
        }

        if (!preg_match('/^[0-9]{10}$/', $mobile)) {
            http_response_code(400);
            respond(false, "Mobile number must contain 10 digits");
        }

        if ($action === "add") {
            $stmt = $conn->prepare(
                "INSERT INTO students (name, email, mobile, course)
                 VALUES (?, ?, ?, ?)"
            );

            $stmt->bind_param(
                "ssss", $name, $email, $mobile, $course
            );

            if ($stmt->execute()) {
                respond(true, "Student added successfully");
            }

            http_response_code(500);
            respond(false, "Unable to add student");
        }

        $id = filter_var(
            $input["id"] ?? null,
            FILTER_VALIDATE_INT
        );

        if (!$id || $id < 1) {
            http_response_code(400);
            respond(false, "Invalid student ID");
        }

        $stmt = $conn->prepare(
            "UPDATE students
             SET name = ?, email = ?, mobile = ?, course = ?
             WHERE id = ?"
        );

        $stmt->bind_param(
            "ssssi", $name, $email, $mobile, $course, $id
        );

        if ($stmt->execute()) {
            respond(true, "Student updated successfully");
        }

        http_response_code(500);
        respond(false, "Unable to update student");
    }

    if ($action === "delete") {
        $id = filter_var(
            $input["id"] ?? null,
            FILTER_VALIDATE_INT
        );

        if (!$id || $id < 1) {
            http_response_code(400);
            respond(false, "Invalid student ID");
        }

        $stmt = $conn->prepare(
            "DELETE FROM students WHERE id = ?"
        );

        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {
            respond(true, "Delete request completed");
        }

        http_response_code(500);
        respond(false, "Unable to delete student");
    }

    http_response_code(400);
    respond(false, "Invalid action");
}

http_response_code(405);
respond(false, "Method not allowed");
?>
