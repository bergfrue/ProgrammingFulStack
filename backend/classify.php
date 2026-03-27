<?php declare(strict_types=1); // Use strict types for safer code ?>
<?php // classify.php — receives text via POST, calls Python classifier, stores result, returns JSON ?>
<?php header('Content-Type: application/json'); // Always return JSON from this endpoint ?>
<?php require_once __DIR__ . '/db.php'; // Load PDO connection from db.php ?>
<?php require_once __DIR__ . '/session.php'; // Load session + CSRF helpers ?>
<?php // Define path to Python executable (change to 'python' on Windows if needed) ?>
<?php $PYTHON_BIN = 'python3'; // Command to invoke Python interpreter ?>
<?php // Build absolute path to the classifier.py script in ../python/ ?>
<?php $PY_SCRIPT  = realpath(__DIR__ . '/../python/classify.py'); // Resolve full path to classifier ?>
<?php // Simple sanitization helper for strings displayed or stored ?>
<?php function clean_text(string $s): string { // Function start
    $t = trim($s); // Remove leading/trailing whitespace
    $t = preg_replace('/\s+/', ' ', $t); // Collapse repeated spaces
    return $t; // Return normalized string
} // End clean_text ?>
<?php // Only accept POST requests for classification to avoid leaking data via GET ?>
<?php if ($_SERVER['REQUEST_METHOD'] !== 'POST') { // If method not POST
    http_response_code(405); // Set HTTP 405 Method Not Allowed
    echo json_encode(['error' => 'Invalid request method']); // Emit error JSON
    exit; // Stop script
} // End method guard ?>
<?php // Optionally enforce CSRF if front-end sends a token (recommended) ?>
<?php $csrf = $_POST['csrf'] ?? ''; // Read CSRF token from POST if present ?>
<?php if ($csrf !== '' && !csrf_verify($csrf)) { // If token is provided but invalid
    http_response_code(400); // Bad request status
    echo json_encode(['error' => 'CSRF validation failed']); // Output error JSON
    exit; // Stop script
} // End CSRF check ?>
<?php // Extract the message text from POST payload ?>
<?php $message = (string)($_POST['message'] ?? ''); // Read message input ?>
<?php $message = clean_text($message); // Normalize whitespace and trim ?>
<?php // Validate message is not empty after cleaning ?>
<?php if ($message === '') { // If no content
    http_response_code(400); // Bad request
    echo json_encode(['error' => 'Message cannot be empty']); // Inform client
    exit; // Stop script
} // End empty check ?>
<?php // Build JSON payload to send to Python via STDIN ?>
<?php $payload = json_encode(['text' => $message], JSON_UNESCAPED_UNICODE); // Encode user text ?>
<?php // Prepare descriptors to communicate with the Python process using proc_open ?>
<?php $descriptors = [ // Begin descriptors
    0 => ['pipe', 'r'], // STDIN pipe (we will write JSON to Python)
    1 => ['pipe', 'w'], // STDOUT pipe (we will read JSON from Python)
    2 => ['pipe', 'w'], // STDERR pipe (capture error messages)
]; // End descriptors ?>
<?php // Build the command string: python3 /abs/path/to/classify.py ?>
<?php $cmd = escapeshellcmd($PYTHON_BIN) . ' ' . escapeshellarg((string)$PY_SCRIPT); // Safe command ?>
<?php // Start the Python process with the specified pipes in the project root ?>
<?php $proc = proc_open($cmd, $descriptors, $pipes, dirname(__DIR__)); // Launch process ?>
<?php if (!is_resource($proc)) { // Ensure process started successfully
    http_response_code(500); // Server error code
    echo json_encode(['error' => 'Failed to spawn classifier']); // Output error
    exit; // Stop script
} // End process check ?>
<?php // Send the JSON payload into Python’s STDIN ?>
<?php fwrite($pipes[0], (string)$payload); // Write encoded JSON to STDIN ?>
<?php fclose($pipes[0]); // Close STDIN to signal end-of-input ?>
<?php // Read Python’s STDOUT (expected to be JSON string) ?>
<?php $out = stream_get_contents($pipes[1]); // Capture classifier output ?>
<?php fclose($pipes[1]); // Close STDOUT pipe ?>
<?php // Read Python’s STDERR (any diagnostics or stack traces) ?>
<?php $err = stream_get_contents($pipes[2]); // Capture error stream ?>
<?php fclose($pipes[2]); // Close STDERR pipe ?>
<?php // Wait for process to end and capture exit code ?>
<?php $exit = proc_close($proc); // Close process and get exit status ?>
<?php // If the classifier exited with non-zero code, return a structured error ?>
<?php if ($exit !== 0) { // Non-success exit code
    http_response_code(500); // Server error
    echo json_encode(['error' => 'Classifier failed', 'code' => $exit, 'stderr' => $err]); // Include diagnostics
    exit; // Stop script
} // End exit-code check ?>
<?php // Decode classifier JSON output into a PHP array ?>
<?php $decoded = json_decode((string)$out, true); // Attempt to decode JSON ?>
<?php if (!is_array($decoded)) { // Validate output shape
    http_response_code(500); // Server error
    echo json_encode(['error' => 'Invalid JSON from classifier', 'raw' => $out]); // Include raw for debugging
    exit; // Stop script
} // End JSON validation ?>
<?php // Extract classification label and score (with sensible defaults) ?>
<?php $label = (string)($decoded['classification'] ?? 'Error'); // Spam/Ham/Error ?>
<?php $score = (float)($decoded['score'] ?? 0.0); // Confidence score as float ?>
<?php // Insert the message and result into database for history/analytics ?>
<?php try { // Try/catch for DB insertion
    $stmt = $pdo->prepare('INSERT INTO messages (text, classification, score) VALUES (:t, :c, :s)'); // Prepare SQL
    $stmt->execute([':t' => $message, ':c' => $label, ':s' => $score]); // Execute with bound params
    $id = (int)$pdo->lastInsertId(); // Retrieve new row id
} catch (Throwable $e) { // Catch DB errors
    http_response_code(500); // Server error
    echo json_encode(['error' => 'DB insert failed', 'details' => $e->getMessage()]); // Output details
    exit; // Stop script
} // End DB try/catch ?>
<?php // Build the success JSON response for the frontend caller ?>
<?php $resp = [ // Start response array
    'status' => 'success', // Indicate success
    'id' => $id, // Newly inserted message id
    'text' => $message, // The analyzed message text
    'classification' => $label, // Spam or Ham
    'score' => $score, // Probability/confidence
    'created_at' => date('Y-m-d H:i:s'), // Timestamp for UI display
]; // End response ?>
<?php echo json_encode($resp); // Emit response as JSON ?>
