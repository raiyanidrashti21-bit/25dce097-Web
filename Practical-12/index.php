
<?php
require 'db.php';

$result = $conn->query("SELECT * FROM events ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Management</title>
</head>
<body>

<h1>Event Management System</h1>

<a href="add_event.php">Add New Event</a>

<hr>

<?php while ($event = $result->fetch_assoc()): ?>

    <h2>
        <?= htmlspecialchars($event['event_name']) ?>
    </h2>

    <p>
        <strong>Date:</strong>
        <?= htmlspecialchars($event['event_date']) ?>
    </p>

    <p>
        <strong>Category:</strong>
        <?= htmlspecialchars($event['category']) ?>
    </p>

    <p>
        <strong>Status:</strong>
        <?= htmlspecialchars($event['status']) ?>
    </p>

    <p>
        <?= nl2br(htmlspecialchars($event['description'])) ?>
    </p>

    <?php if (!empty($event['poster'])): ?>
        <img
            src="uploads/<?= rawurlencode($event['poster']) ?>"
            alt="Event poster"
            width="200">
    <?php endif; ?>

    <a href="edit_event.php?id=<?= (int)$event['id'] ?>">Edit</a>

    <form action="delete_event.php" method="post"
          onsubmit="return confirm('Are you sure you want to delete this event?');">
        <input type="hidden" name="id" value="<?= (int)$event['id'] ?>">
        <button type="submit">Delete</button>
    </form>

    <hr>

<?php endwhile; ?>

</body>
</html>
