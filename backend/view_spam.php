<?php
// Show errors so you can debug
error_reporting(E_ALL);
ini_set('display_errors', 1);

// FIXED include path
include "db.php";  // correct path because this file is also inside backend/

// Query database
$sql = "SELECT * FROM spam_messages ORDER BY id DESC";
$result = $conn->query($sql);

if (!$result) {
    die("SQL ERROR: " . $conn->error);
}

echo "<h1>Stored Spam Detection Records</h1>";
echo "<table border='1' cellpadding='8'>";
echo "<tr>
        <th>ID</th>
        <th>Message</th>
        <th>Spam Score</th>
        <th>Spam?</th>
        <th>Date</th>
      </tr>";

while ($row = $result->fetch_assoc()) {
    echo "<tr>";
    echo "<td>".$row['id']."</td>";
    echo "<td>".$row['message']."</td>";
    echo "<td>".$row['spam_score']."</td>";
    echo "<td>".($row['is_spam'] ? "Yes" : "No")."</td>";
    echo "<td>".$row['created_at']."</td>";
    echo "</tr>";
}

echo "</table>";
?>
