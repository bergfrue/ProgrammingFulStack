<?php declare(strict_types=1); // Enforce strict types ?>
<?php // session.php — shared session utilities: start session, CSRF helpers, flash messages ?>
<?php if (session_status() !== PHP_SESSION_ACTIVE) { session_start(); } // Ensure session started ?>
<?php // Generate or retrieve CSRF token from session for form protection ?>
<?php function csrf_token(): string { // Function start
    if (!isset($_SESSION['csrf'])) { // If token not present
        $_SESSION['csrf'] = bin2hex(random_bytes(16)); // Create a random 32-hex token
    } // End generation
    return (string)$_SESSION['csrf']; // Return token string
} // End csrf_token ?>
<?php // Verify CSRF token provided by client equals session token ?>
<?php function csrf_verify(?string $token): bool { // Function start
    return isset($_SESSION['csrf']) // Ensure a session token exists
        && is_string($token) // Ensure provided token is a string
        && hash_equals($_SESSION['csrf'], $token); // Constant-time compare
} // End csrf_verify ?>
<?php // Add a flash message to session to display after redirect or next load ?>
<?php function flash(string $type, string $message): void { // Function start
    $_SESSION['__flash'][] = ['t' => $type, 'm' => $message]; // Push a typed message
} // End flash ?>
<?php // Retrieve and clear flash messages for current request ?>
<?php function flash_read(): array { // Function start
    $msgs = $_SESSION['__flash'] ?? []; // Get array or empty
    unset($_SESSION['__flash']); // Clear messages after reading
    return $msgs; // Return collected messages
} // End flash_read ?>
