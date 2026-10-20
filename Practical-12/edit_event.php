
<?php
require 'db.php';

$error = '';
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id || $id < 1) {
    die("Invalid event ID.");
}

$stmt = $conn->prepare("SELECT * FROM events WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$event = $result->fetch_assoc();

if (!$event) {
    die("Event not found.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['event_name'] ?? '');
    $date = $_POST['event_date'] ?? '';
    $description = trim($_POST['description'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $status = $_POST['status'] ?? 'open';
    $poster = $event['poster'];

    if ($name === '' || $date === '' || $description === '') {
        $error = "Please fill in all required fields.";
    } elseif (strlen($name) > 100 || strlen($description) > 255 || strlen($category) > 100) {
        $error = "Some text exceeds the allowed length.";
    } elseif (!in_array($status, ['open', 'closed'], true)) {
        $error = "Invalid status.";
    } else {
        $dateObject = DateTime::createFromFormat('!Y-m-d', $date);

        if (!$dateObject || $dateObject->format('Y-m-d') !== $date) {
            $error = "Invalid event date.";
        }
    }

    $newPoster = null;

    if (
        $error === '' &&
        isset($_FILES['poster']) &&
        $_FILES['poster']['error'] !== UPLOAD_ERR_NO_FILE
    ) {
        $file = $_FILES['poster'];

        if ($file['error'] !== UPLOAD_ERR_OK) {
            $error = "Poster upload failed.";
        } elseif ($file['size'] > 2 * 1024 * 1024) {
            $error = "Poster must be 2 MB or smaller.";
        } else {
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $mime = $finfo->file($file['tmp_name']);

            $allowed = [
                'image/jpeg' => 'jpg',
                'image/png' => 'png',
                'image/gif' => 'gif',
                'image/webp' => 'webp'
            ];

            if (!isset($allowed[$mime]) || !@getimagesize($file['tmp_name'])) {
                $error = "Only valid JPG, PNG, GIF, or WebP images are allowed.";
            } else {
                $newPoster = bin2hex(random_bytes(16)) . '.' . $allowed[$mime];

                if (!move_uploaded_file(
                    $file['tmp_name'],
                    __DIR__ . '/uploads/' . $newPoster
                )) {
                    $error = "Could not save the poster.";
                } else {
                    $poster = $newPoster;
                }
            }
        }
    }

    if ($error === '') {
        $stmt = $conn->prepare(
            "UPDATE events
             SET event_name = ?, event_date = ?, description = ?,
                 category = ?, poster = ?, status = ?
             WHERE id = ?"
        );

        $stmt->bind_param(
            "ssssssi",
            $name,
            $date,
            $description,
            $category,
            $poster,
            $status,
            $id
        );

        if ($stmt->execute()) {
            if ($newPoster !== null && !empty($event['poster'])) {
                $oldPath = __DIR__ . '/uploads/' . $event['poster'];

                if (is_file($oldPath)) {
                    unlink($oldPath);
                }
            }

            header("Location: index.php?message=updated");
            exit;
        }

        if ($newPoster !== null) {
            $newPath = __DIR__ . '/uploads/' . $newPoster;

            if (is_file($newPath)) {
                unlink($newPath);
            }
        }

        $error = "Could not update the event.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Event</title>
</head>
<body>

<h1>Edit Event</h1>

<?php if ($error !== ''): ?>
    <p style="color: red;">
        <?= htmlspecialchars($error) ?>
    </p>
<?php endif; ?>

<form method="post" enctype="multipart/form-data">

    <label>Event Name:</label><br>
    <input type="text" name="event_name"
           value="<?= htmlspecialchars($event['event_name']) ?>"
           maxlength="100" required><br><br>

    <label>Event Date:</label><br>
    <input type="date" name="event_date"
           value="<?= htmlspecialchars($event['event_date']) ?>"
           required><br><br>

    <label>Description:</label><br>
    <textarea name="description" maxlength="255"
              required><?= htmlspecialchars($event['description']) ?></textarea><br><br>

    <label>Category:</label><br>
    <input type="text" name="category"
           value="<?= htmlspecialchars($event['category'] ?? '') ?>"
           maxlength="100"><br><br>

    <label>Status:</label><br>
    <select name="status">
        <option value="open" <?= $event['status'] === 'open' ? 'selected' : '' ?>>Open</option>
        <option value="closed" <?= $event['status'] === 'closed' ? 'selected' : '' ?>>Closed</option>
    </select><br><br>

    <?php if (!empty($event['poster'])): ?>
        <p>Current poster:</p>
        <img src="uploads/<?= rawurlencode($event['poster']) ?>"
             alt="Current event poster" width="180"><br><br>
    <?php endif; ?>

    <label>Replace Poster (optional, maximum 2 MB):</label><br>
    <input type="file" name="poster"
           accept=".jpg,.jpeg,.png,.gif,.webp"><br><br>

    <button type="submit">Update Event</button>

</form>

<br>
<a href="index.php">Back to Events</a>

</body>
</html>
