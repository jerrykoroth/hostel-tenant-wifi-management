<?php
function check_login() {
    if (!isset($_SESSION['alogin'])) {
        header("Location: index.php");
        exit;
    }
}
?>