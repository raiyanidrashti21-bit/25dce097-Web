
<?php
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

if (!$id || $id < 1) {
    die("Invalid event ID.");
}

// Find the event and its poster.
$stmt = $conn->prepare("SELECT poster FROM events WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$event = $stmt->get_result()->fetch_assoc();

if (!$event) {
    die("Event not found.");
}

// Check whether registrations use this event.
$stmt = $conn->prepare(
    "SELECT COUNT(*) AS total
     FROM registration
     WHERE event_id = ?"
);
$stmt->bind_param("i", $id);
$stmt->execute();
$registration = $stmt->get_result()->fetch_assoc();

if ((int)$registration['total'] > 0) {
    die(
        "Cannot delete this event because registrations exist. "
        . "Keep the event or handle its registrations first."
    );
}

// Delete the event only when it has no registrations.
$stmt = $conn->prepare("DELETE FROM events WHERE id = ?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    if (!empty($event['poster'])) {
        $posterPath = __DIR__ . '/uploads/' . basename($event['poster']);

        if (is_file($posterPath)) {
            unlink($posterPath);
        }
    }

    header('Location: index.php?message=deleted');
    exit;
}

die("Could not delete the event.");
?>
