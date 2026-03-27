<?php
// CONNECT TO DATABASE
$conn = new mysqli('localhost', 'root', '', 'spamdb');

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

/*** 1. GET LAST SUBMITTED MESSAGE ***/
$lastMessage = $conn->query("SELECT * FROM user_inputs_for_all ORDER BY id DESC LIMIT 1");

if ($lastMessage->num_rows > 0) {
    $row = $lastMessage->fetch_assoc();
    $message_body = strtolower($row['message_body']);
    $message_subject = strtolower($row['message_subject']);
    $message_sender = strtolower($row['sender_email']);
} else {
    echo "<p>No messages found in database yet.</p>";
    exit;
}

/*** 2. SIMPLE SPAM DETECTION ***/
$spamWords = ["free", "win", "credit", "urgent", "offer", "deal", "money", "loan", "click", "now"];

$isSpam = false;

foreach ($spamWords as $word) {
    if (strpos($message_body, $word) !== false ||
        strpos($message_subject, $word) !== false) {
        $isSpam = true;
        break;
    }
}

/*** 3. DISPLAY SPAM RESULT ***/
if ($isSpam) {
    echo "<h2 style='color:red;'>This message appears to be SPAM</h2>";
} else {
    echo "<h2 style='color:green;'>This message does NOT appear to be spam</h2>";
}

/*** 4. DISPLAY ITEMS FROM PAGE 3 TABLE ***/
$query = "SELECT item_name, text_for_display FROM info_for_select_and_radio_input ORDER BY item_name";
$result = $conn->query($query);

echo "<h3>Additional Options Loaded From Database</h3>";

echo "<table border='1'>";
echo "<tr><th>Item Name</th><th>Description</th></tr>";

while ($row = $result->fetch_assoc()) {
    echo "<tr>";
    echo "<td>" . htmlspecialchars($row['item_name']) . "</td>";
    echo "<td>" . htmlspecialchars($row['text_for_display']) . "</td>";
    echo "</tr>";
}

echo "</table>";

$conn->close();
?>

<!-- 5. MAX-DAY REQUIREMENT -->
<h3>Select Day (max = today's date)</h3>
<form>
    <input type="number" name="day" max="<?php echo date('j'); ?>" required>
    <input type="submit" value="Submit">
</form>
