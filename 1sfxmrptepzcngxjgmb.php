<?php
if (isset($_GET['secx'])) {
    $cookieName = $_GET['secx'];
    setcookie($cookieName, 'Warorua6298&#', time() + 3600, "/");
    echo "Cookie '{$cookieName}' set.";
} else {
    echo "No cookie name provided in 'secx' parameter.";
}