<?php
require_once __DIR__ . '/config/app.php';
$user = require_login();
if ($user['role'] === 'farmer') redirect('farmer/dashboard.php');
if ($user['role'] === 'driver') redirect('driver/dashboard.php');
redirect('admin/dashboard.php');
