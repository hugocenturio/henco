<?php

/**
 * Fetch a single setting value from the settings table.
 *
 * @param mysqli $mysqli
 * @param string $key
 * @param string $default  Returned when the key is not found or the value is empty.
 * @return string
 */
function get_setting($mysqli, $key, $default = '')
{
    $stmt = $mysqli->prepare('SELECT setting_value FROM settings WHERE setting_key = ? LIMIT 1');
    $stmt->bind_param('s', $key);
    $stmt->execute();
    $stmt->bind_result($value);
    $stmt->fetch();
    $stmt->close();
    return ($value !== null && $value !== '') ? $value : $default;
}

/**
 * Insert or update a setting in the settings table.
 */
function update_setting($mysqli, $key, $value)
{
    $stmt_check = $mysqli->prepare('SELECT id FROM settings WHERE setting_key = ?');
    $stmt_check->bind_param('s', $key);
    $stmt_check->execute();
    $stmt_check->store_result();

    if ($stmt_check->num_rows > 0) {
        $stmt = $mysqli->prepare('UPDATE settings SET setting_value = ? WHERE setting_key = ?');
        $stmt->bind_param('ss', $value, $key);
        $stmt->execute();
        $stmt->close();
    } else {
        $stmt = $mysqli->prepare('INSERT INTO settings (setting_key, setting_value) VALUES (?, ?)');
        $stmt->bind_param('ss', $key, $value);
        $stmt->execute();
        $stmt->close();
    }

    $stmt_check->close();
}

// -------------------------------------------------------
// CSRF helpers
// -------------------------------------------------------

/**
 * Return the current session CSRF token, generating one if needed.
 * Requires an active session.
 */
function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Return a hidden CSRF input field for use inside HTML forms.
 */
function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') . '">';
}

/**
 * Verify that the POST request contains a valid CSRF token.
 * Redirects with an error message if invalid.
 */
function csrf_verify(): void
{
    if (
        !isset($_POST['csrf_token']) ||
        !hash_equals(csrf_token(), $_POST['csrf_token'])
    ) {
        $_SESSION['error_message'] = 'Invalid request. Please try again.';
        $redirect = strtok($_SERVER['REQUEST_URI'], '?');
        header('Location: ' . $redirect);
        exit();
    }
}
