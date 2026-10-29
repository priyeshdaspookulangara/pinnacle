<?php
// public/index.php

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../src/Controllers/AuthController.php';
require_once __DIR__ . '/../src/Controllers/UserController.php';
require_once __DIR__ . '/../src/Controllers/AdminController.php';

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
} elseif ($action === 'request_withdrawal') {
    $userController = new UserController();
    $userController->requestWithdrawal();
} elseif ($action === 'update_settings') {
    $adminController = new AdminController();
    $adminController->updateSettings();
} elseif ($action === 'process_withdrawal') {
    $adminController = new AdminController();
    $adminController->processWithdrawal();
} elseif ($action === 'export_csv') {
    $adminController = new AdminController();
    $adminController->exportCsv();
} elseif ($action === 'download_pdf' || $action === 'download_brochure') {
    $userController = new UserController();
    $userController->downloadPdfBrochure();
}

// Page Handlers
if ($page === 'login') {
    $authController->showLogin();
} elseif ($page === 'register') {
    $authController->showRegister();
} elseif ($page === 'dashboard') {
    $userController = new UserController();
    $userController->dashboard();
} elseif ($page === 'genealogy') {
    $userController = new UserController();
    $userController->genealogy();
} elseif ($page === 'ledger') {
    $userController = new UserController();
    $userController->ledger();
} elseif ($page === 'business_plan' || $page === 'brochure') {
    $userController = new UserController();
    $userController->businessPlan();
} elseif ($page === 'admin_dashboard') {
    $adminController = new AdminController();
    $adminController->dashboard();
} elseif ($page === 'admin_settings') {
    $adminController = new AdminController();
    $adminController->settings();
} elseif ($page === 'admin_members') {
    $adminController = new AdminController();
    $adminController->members();
} elseif ($page === 'admin_matrix_tree') {
    $adminController = new AdminController();
    $adminController->matrixTree();
} else {
    // Default Route
    if (isLoggedIn()) {
        $user = currentUser();
        if ($user['role'] === 'admin') {
            header("Location: /index.php?page=admin_dashboard");
        } else {
            header("Location: /index.php?page=dashboard");
        }
    } else {
        header("Location: /index.php?page=login");
    }
    exit();
}
