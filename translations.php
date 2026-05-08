<?php
$translations = [];
$translation_file = "locales/{$_SESSION['locale']}.json"; // Assuming translations are stored in JSON files
if (file_exists($translation_file)) {
    $translations = json_decode(file_get_contents($translation_file), true);
}

// Function to fetch translation
function translate($key, $translations) {
    return $translations[$key] ?? $key;
}  
?>