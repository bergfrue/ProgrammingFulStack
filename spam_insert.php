<?php
include "backend/db.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Sanitize text message
    $message = $conn->real_escape_string($_POST['message']);

    // Simple spam detection
    $keywords = ['free', 'win', 'offer', 'click', 'money', 'prize', 'congratulations'];
    $spam_score = 0;

    foreach ($keywords as $word) {
        if (stripos($message, $word) !== false) {
            $spam_score += 20;
        }
    }

    $is_spam = ($spam_score >= 40) ? 1 : 0;

    // INSERT into database (assignment: “store page 2 items”)
    $sql = "INSERT INTO spam_messages (message, spam_score, is_spam)
            VALUES ('$message', '$spam_score', '$is_spam')";
    $conn->query($sql);

    echo "<h2>Message Stored Successfully</h2>";
    echo "<p><strong>Your Message:</strong> $message</p>";
    echo "<p><strong>Spam Score:</strong> $spam_score</p>";
    echo "<p><strong>Status:</strong> " . ($is_spam ? "SPAM" : "Not Spam") . "</p>";

    echo "<br><br><a href='view_spam.php'>View All Stored Messages (Page 3)</a>";
}
?>
