<?php
require_once 'config/database.php';
require_once 'includes/functions.php';
require_once 'includes/check_auth.php';

// Manejar la solicitud
$url = isset($_GET['url']) ? $_GET['url'] : '';
switch ($url) {
    case '':
        include 'client/dashboard.php';
        break;
    case 'requests':
        include 'client/requests.php';
        break;
    case 'new_request':
        include 'client/new_request.php';
        break;
    case 'auth/login':
        include 'auth/login.php';
        break;
    case 'auth/register':
        include 'auth/register.php';
        break;
    default:
        include '404.php';
        break;
}
?>
