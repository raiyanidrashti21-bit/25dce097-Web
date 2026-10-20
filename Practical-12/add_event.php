
<?php
require 'db.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['event_name'] ?? '');
    $date = $_POST['event_date'] ?? '';
    $description = trim($_POST['description'] ?? '');
    $category = trim($_POST['category'] ?? 'General');
    $status = $_POST['status'] ?? 'open';
    $poster = null;

    if ($name === '' || $date === '' || $description === '') {
        $error = 'Please fill in all required fields.';
    } elseif (strlen($category) > 100) {
        $error = 'Category must not exceed 100 characters.';
    } elseif (!in_array($status, ['open', 'closed'], true)) {
        $error = 'Invalid event status.';
    } else {
        $dateObject = DateTime::createFromFormat('!Y-m-d', $date);

        if (!$dateObject || $dateObject->format('Y-m-d') !== $date) {
            $error = 'Invalid event date.';
        }
    }

    if (
        $error === '' &&
        isset($_FILES['poster']) &&
        $_FILES['poster']['error'] !== UPLOAD_ERR_NO_FILE
    ) {
        $file = $_FILES['poster'];

        if ($file['error'] !== UPLOAD_ERR_OK) {
            $error = 'Poster upload failed.';
        } elseif ($file['size'] > 2 * 1024 * 1024) {
            $error = 'Poster must be 2 MB or smaller.';
        } else {
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $mime = $finfo->file($file['tmp_name']);

            $allowed = [
                'image/jpeg' => 'jpg',
                'image/png' => 'png',
                'image/gif' => 'gif',
                'image/webp' => 'webp'
            ];

            if (
                !isset($allowed[$mime]) ||
                !@getimagesize($file['tmp_name'])
            ) {
                $error = 'Only valid JPG, PNG, GIF, or WebP images are allowed.';
            } else {
                $poster = bin2hex(random_bytes(16))
                    . '.' . $allowed[$mime];

                if (!move_uploaded_file(
                    $file['tmp_name'],
                    __DIR__ . '/uploads/' . $poster
                )) {
                    $error = 'Could not save the poster.';
                }
            }
        }
    }

    if ($error === '') {
        $stmt = $conn->prepare(
            "INSERT INTO events
            (event_name, event_date, description, category, poster, status)
            VALUES (?, ?, ?, ?, ?, ?)"
        );

        $stmt->bind_param(
            "ssssss",
            $name,
            $date,
            $description,
            $category,
            $poster,
            $status
        );

        if ($stmt->execute()) {
            header('Location: index.php?message=added');
            exit;
        }

        if ($poster !== null) {
            $path = __DIR__ . '/uploads/' . $poster;

            if (is_file($path)) {
                unlink($path);
            }
        }

        $error = 'Could not save the event.';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Event</title>
</head>
<body>

<h1>Add New Event</h1>

<?php if ($error !== ''): ?>
    <p style="color: red;">
        <?= htmlspecialchars($error) ?>
    </p>
<?php endif; ?>

<form method="post" enctype="multipart/form-data">

    <label>Event Name:</label><br>
    <input type="text" name="event_name" required maxlength="100"><br><br>

    <label>Event Date:</label><br>
    <input type="date" name="event_date" required><br><br>

    <label>Description:</label><br>
    <textarea name="description" required maxlength="255"></textarea><br><br>

    <label>Category:</label><br>
    <input type="text" name="category" value="General" maxlength="100"><br><br>

    <label>Status:</label><br>
    <select name="status">
        <option value="open">Open</option>
        <option value="closed">Closed</option>
    </select><br><br>

    <label>Event Poster (JPG, PNG, GIF, WebP; maximum 2 MB):</label><br>
    <input type="file" name="poster"
           accept=".jpg,.jpeg,.png,.gif,.webp"><br><br>

    <button type="submit">Save Event</button>

</form>

<br>
<a href="index.php">Back to Events</a>

</body>
</html>
