<?php
include "backend/db.php";

$result = $conn->query("SELECT * FROM spam_messages ORDER BY id DESC");

echo "<h1>Stored Spam Messages</h1>";

echo "<table border='1' cellpadding='10' cellspacing='0'>";
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
    echo "<td>".($row['is_spam'] ? 'Yes' : 'No')."</td>";
    echo "<td>".$row['created_at']."</td>";
    echo "</tr>";
}

echo "</table>";

?>
