<?php
session_start();
require_once __DIR__ . '/config/app.php';
$_SESSION['user'] = ['id' => 2, 'username' => 'userA', 'full_name' => 'User A', 'email' => 'usera@example.com', 'role' => 'user', 'wallet_balance' => 100, 'reserve_balance' => 0];
$_GET['page'] = 'referrals';
require_once __DIR__ . '/src/Controllers/UserController.php';
$controller = new UserController();
$controller->referrals();
