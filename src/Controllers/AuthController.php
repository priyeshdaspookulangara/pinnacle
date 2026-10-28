<?php
// src/Controllers/AuthController.php

require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../Models/User.php';

class AuthController {
    private User $userModel;

    public function __construct() {
        $this->userModel = new User();
    }

    public function showLogin() {
        if (isLoggedIn()) {
            $user = currentUser();
            if ($user['role'] === 'admin') {
                header("Location: /index.php?page=admin_dashboard");
            } else {
                header("Location: /index.php?page=dashboard");
            }
            exit();
        }
        require_once __DIR__ . '/../../views/auth/login.php';
    }

    public function processLogin() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: /index.php?page=login");
            exit();
        }

        if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            $_SESSION['error'] = "Invalid CSRF token.";
            header("Location: /index.php?page=login");
            exit();
        }

        $login = trim($_POST['login'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($login) || empty($password)) {
            $_SESSION['error'] = "Please fill in all fields.";
            header("Location: /index.php?page=login");
            exit();
        }

        $user = $this->userModel->authenticate($login, $password);
        if (!$user) {
            $_SESSION['error'] = "Invalid username/email or password.";
            header("Location: /index.php?page=login");
            exit();
        }

        if ($user['status'] !== 'active') {
            $_SESSION['error'] = "Your account is currently suspended.";
            header("Location: /index.php?page=login");
            exit();
        }

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_role'] = $user['role'];

        if ($user['role'] === 'admin') {
            header("Location: /index.php?page=admin_dashboard");
        } else {
            header("Location: /index.php?page=dashboard");
        }
        exit();
    }

    public function showRegister() {
        if (isLoggedIn()) {
            header("Location: /index.php?page=dashboard");
            exit();
        }
        $refUsername = trim($_GET['ref'] ?? '');
        $sponsor = null;
        if (!empty($refUsername)) {
            $sponsor = $this->userModel->findByUsername($refUsername);
        }
        require_once __DIR__ . '/../../views/auth/register.php';
    }

    public function processRegister() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: /index.php?page=register");
            exit();
        }

        if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            $_SESSION['error'] = "Invalid CSRF token.";
            header("Location: /index.php?page=register");
            exit();
        }

        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $fullName = trim($_POST['full_name'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        $sponsorUsername = trim($_POST['sponsor'] ?? '');

        if (empty($username) || empty($email) || empty($fullName) || empty($password)) {
            $_SESSION['error'] = "Required fields cannot be empty.";
            header("Location: /index.php?page=register");
            exit();
        }

        if ($password !== $confirmPassword) {
            $_SESSION['error'] = "Passwords do not match.";
            header("Location: /index.php?page=register");
            exit();
        }

        if ($this->userModel->findByUsername($username)) {
            $_SESSION['error'] = "Username is already taken.";
            header("Location: /index.php?page=register");
            exit();
        }

        if ($this->userModel->findByEmail($email)) {
            $_SESSION['error'] = "Email address is already registered.";
            header("Location: /index.php?page=register");
            exit();
        }

        $sponsorId = null;
        if (!empty($sponsorUsername)) {
            $sponsor = $this->userModel->findByUsername($sponsorUsername);
            if ($sponsor) {
                $sponsorId = (int)$sponsor['id'];
            }
        }

        $newUserId = $this->userModel->register($username, $email, $password, $fullName, $phone, $sponsorId);

        $_SESSION['user_id'] = $newUserId;
        $_SESSION['user_role'] = 'user';
        $_SESSION['success'] = "Registration successful! Welcome to Pinnacle Hunt.";

        header("Location: /index.php?page=dashboard");
        exit();
    }

    public function logout() {
        session_destroy();
        header("Location: /index.php?page=login");
        exit();
    }
}
