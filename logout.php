<?php
require_once __DIR__ . '/config/app.php';
$_SESSION = [];
session_destroy();
header('Location: ' . base_url('login.php'));
exit;
