<?php
include 'header.php';
include 'translations.php';
require_once 'helpers.php';
$page_title = 'Settings';

// Check if user is logged in and if the user is admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role_id']) || $_SESSION['role_id'] != 1) {
    header('Location: dashboard.php');
    exit();
}

// Create the 'settings' table if it does not exist
$create_settings_table_sql = "CREATE TABLE IF NOT EXISTS settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(255) NOT NULL,
    setting_value VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)";
$mysqli->query($create_settings_table_sql);

// Handle form submission to update all settings
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $settings = [
        'manager_email' => [
            'value' => trim($_POST['manager_email'] ?? ''),
            'validation' => function ($value) {
                return filter_var($value, FILTER_VALIDATE_EMAIL);
            },
            'error_message' => translate('emailValidate', $translations)
        ],
        'send_email' => [
            'value' => trim($_POST['send_email'] ?? ''),
            'validation' => function ($value) {
                return filter_var($value, FILTER_VALIDATE_EMAIL);
            },
            'error_message' => translate('emailValidate', $translations)
        ],
        'currency' => [
            'value' => trim($_POST['currency'] ?? ''),
            'validation' => function ($value) {
                return !empty($value);
            },
            'error_message' => translate('currencyValidate', $translations)
        ],
        'locale' => [
            'value' => trim($_POST['locale'] ?? ''),
            'validation' => function ($value) {
                return !empty($value);
            },
            'error_message' => translate('localeValidate', $translations)
        ],
        'company_name' => [
            'value' => trim($_POST['company_name'] ?? ''),
            'validation' => function ($value) {
                return !empty($value);
            },
            'error_message' => translate('companyValidate', $translations)
        ]
    ];

    $errors = [];
    foreach ($settings as $key => $setting) {
        if (!$setting['validation']($setting['value'])) {
            $errors[] = $setting['error_message'];
        } else {
            update_setting($mysqli, $key, $setting['value']);
            if ($key === 'locale') {
                $_SESSION['locale'] = $setting['value'];
            } elseif ($key === 'company_name') {
                $_SESSION['company_name'] = $setting['value'];
            }
        }
    }

    if (empty($errors)) {
        $_SESSION['success_message'] = translate('updateSettings', $translations);
    } else {
        $_SESSION['error_message'] = implode('<br>', $errors);
    }
}

// Get the current settings
$manager_email = get_setting($mysqli, 'manager_email');
$send_email    = get_setting($mysqli, 'send_email');
$currency      = get_setting($mysqli, 'currency', '€');
$locale        = get_setting($mysqli, 'locale', 'en_US');
$company_name  = get_setting($mysqli, 'company_name');

// Close the database connection
$mysqli->close();
include 'template.php';
?>

<div class="row">
    <h1 data-translate="settings">Settings</h1>

    <!-- Unified form to update all settings -->
    <form method="POST" action="">
        <div class="mb-4">
            <label for="managerEmail" class="form-label" data-translate="managerEmail">Manager Email:</label>
            <input type="email" id="managerEmail" name="manager_email" class="form-control"
                   value="<?php echo htmlspecialchars($manager_email, ENT_QUOTES, 'UTF-8'); ?>" required>
        </div>

        <div class="mb-4">
            <label for="sendEmail" class="form-label" data-translate="sendEmail">Send Email (From):</label>
            <input type="email" id="sendEmail" name="send_email" class="form-control"
                   value="<?php echo htmlspecialchars($send_email, ENT_QUOTES, 'UTF-8'); ?>" required>
        </div>

        <div class="mb-4">
            <label for="currency" class="form-label" data-translate="currency">Currency:</label>
            <input type="text" id="currency" name="currency" class="form-control"
                   value="<?php echo htmlspecialchars($currency, ENT_QUOTES, 'UTF-8'); ?>" required>
        </div>

        <div class="mb-4">
            <label for="locale" class="form-label" data-translate="locale">Locale:</label>
            <input type="text" id="locale" name="locale" class="form-control"
                   value="<?php echo htmlspecialchars($locale, ENT_QUOTES, 'UTF-8'); ?>" required>
        </div>

        <div class="mb-4">
            <label for="companyName" class="form-label" data-translate="companyName">Company Name:</label>
            <input type="text" id="companyName" name="company_name" class="form-control"
                   value="<?php echo htmlspecialchars($company_name, ENT_QUOTES, 'UTF-8'); ?>" required>
        </div>

        <button type="submit" class="btn btn-primary" data-translate="update">Update</button>
    </form>
</div>
<?php include 'footer.php'; ?>
