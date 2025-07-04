<?php
$hash = '$2y$10$wHfXfE3EB4h8V6IbRHfWJOsUZmkHiVim2k8Sx06aTCWhWmHAB7Uwa';
$password = 'admin123';

if (password_verify($password, $hash)) {
    echo "Password matches!";
} else {
    echo "Password does NOT match.";
}
?>
