<?php declare(strict_types=1); // Strict typing on ?>
<?php // feedback.php — marks a stored classification as correct or wrong for future retraining ?>
<?php header('Content-Type: application/json'); // Always return JSON from this endpoint ?>
<?php require_once __DIR__ . '/db.php'; // Bring in PDO connection ?>
<?php require_once __DIR__ . '/session.php'; // Load session + CSRF helpers ?>
<?php // Only accept POST to modify server state safely ?>
<?php if ($_SERVER['REQUEST_METHOD'] !== 'POST') { // Enforce POST
    http_response_code(405); // Method not allowed
    echo json_encode(['error' => 'Invalid request method']); // Error payload
    exit; // Stop execution
} // End method check ?>
<?php // Read and optionally verify CSRF token if provided ?>
<?php $csrf = $_POST['csrf'] ?? ''; // Pull token from POST ?>
<?php if ($csrf !== '' && !csrf_verify($csrf)) { // If token present but invalid
    http_response_code(400); // Bad request
    echo json_encode(['error' => 'CSRF validation failed']); // Error JSON
    exit; // Stop execution
} // End CSRF branch ?>
<?php // Pull message id from POST payload ?>
<?php $id = isset($_POST['id']) ? (int)$_POST['id'] : 0; // Cast to integer ?>
<?php // Pull feedback value from POST payload ?>
<?php $feedback = (string)($_POST['feedback'] ?? ''); // Expected 'correct' or 'wrong' ?>
<?php // Validate id and feedback inputs ?>
<?php if ($id <= 0 || !in_array($feedback, ['correct','wrong'], true)) { // Basic validation
    http_response_code(400); // Bad request
    echo json_encode(['error' => 'Invalid id or feedback']); // Error JSON
    exit; // Stop script
} // End validation ?>
<?php // Attempt to update the database row with the feedback label ?>
<?php try { // Start try/catch
    $stmt = $pdo->prepare('UPDATE messages SET feedback=:f WHERE id=:i'); // Prepare SQL update
    $stmt->execute([':f' => $feedback, ':i' => $id]); // Execute with bound parameters
    $changed = $stmt->rowCount(); // Count affected rows
} catch (Throwable $e) { // Catch DB exceptions
    http_response_code(500); // Server error
    echo json_encode(['error' => 'DB update failed', 'details' => $e->getMessage()]); // Return error JSON
    exit; // Stop execution
} // End try/catch ?>
<?php // If no rows changed, the id likely didn’t exist ?>
<?php if ($changed === 0) { // No update happened
    http_response_code(404); // Not found
    echo json_encode(['error' => 'Message not found']); // Inform client
    exit; // Stop script
} // End not-found check ?>
<?php // Build success response indicating feedback saved ?>
<?php echo json_encode(['status' => 'ok', 'id' => $id, 'feedback' => $feedback]); // Emit JSON ?>
