<?php
// index.php - Main entry point

require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/src/Controllers/AuthController.php';
require_once __DIR__ . '/src/Controllers/UserController.php';
require_once __DIR__ . '/src/Controllers/AdminController.php';

$page = $_GET['page'] ?? '';
$action = $_GET['action'] ?? '';

$authController = new AuthController();

// Action Handlers
if ($action === 'login') {
    $authController->processLogin();
} elseif ($action === 'register') {
    $authController->processRegister();
} elseif ($action === 'logout') {
    $authController->logout();
} elseif ($action === 'purchase_package') {
    $userController = new UserController();
    $userController->purchasePackage();
} elseif ($action === 'submit_usdt_payment') {
    $userController = new UserController();
    $userController->submitUsdtPayment();
} elseif ($action === 'update_profile_usdt') {
    $userController = new UserController();
    $userController->updateProfileUsdt();
} elseif ($action === 'request_withdrawal') {
    $userController = new UserController();
    $userController->requestWithdrawal();
} elseif ($action === 'update_settings') {
    $adminController = new AdminController();
    $adminController->updateSettings();
} elseif ($action === 'process_withdrawal') {
    $adminController = new AdminController();
    $adminController->processWithdrawal();
} elseif ($action === 'process_usdt_activation') {
    $adminController = new AdminController();
    $adminController->processUsdtActivation();
} elseif ($action === 'edit_member') {
    $adminController = new AdminController();
    $adminController->editMember();
} elseif ($action === 'toggle_member_status') {
    $adminController = new AdminController();
    $adminController->toggleMemberStatus();
} elseif ($action === 'delete_member') {
    $adminController = new AdminController();
    $adminController->deleteMember();
} elseif ($action === 'export_csv') {
    $adminController = new AdminController();
    $adminController->exportCsv();
}

// Page Handlers
if ($page === 'login') {
    $authController->showLogin();
} elseif ($page === 'register') {
    $authController->showRegister();
} elseif ($page === 'dashboard') {
    $userController = new UserController();
    $userController->dashboard();
} elseif ($page === 'usdt_activate') {
    $userController = new UserController();
    $userController->usdtActivationPage();
} elseif ($page === 'genealogy') {
    $userController = new UserController();
    $userController->genealogy();
} elseif ($page === 'referrals') {
    $userController = new UserController();
    $userController->referrals();
} elseif ($page === 'ledger') {
    $userController = new UserController();
    $userController->ledger();
} elseif ($page === 'admin_dashboard') {
    $adminController = new AdminController();
    $adminController->dashboard();
} elseif ($page === 'admin_settings') {
    $adminController = new AdminController();
    $adminController->settings();
} elseif ($page === 'admin_members') {
    $adminController = new AdminController();
    $adminController->members();
} elseif ($page === 'admin_usdt') {
    $adminController = new AdminController();
    $adminController->usdtActivations();
} elseif ($page === 'admin_matrix_tree') {
    $adminController = new AdminController();
    $adminController->matrixTree();
} else {
    // Default Route
    if (isLoggedIn()) {
        $user = currentUser();
        if ($user['role'] === 'admin') {
            header("Location: index.php?page=admin_dashboard");
        } else {
            header("Location: index.php?page=dashboard");
        }
    } else {
        header("Location: index.php?page=login");
    }
    exit();
}
