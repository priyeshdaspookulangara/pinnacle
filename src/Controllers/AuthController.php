<?php
// src/Controllers/AuthController.php

require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../Models/User.php';
require_once __DIR__ . '/../Models/Package.php';
require_once __DIR__ . '/../Models/Settings.php';
require_once __DIR__ . '/../Models/UsdtPayment.php';

class AuthController {
    private User $userModel;

    public function __construct() {
        $this->userModel = new User();
    }

    public function showLogin() {
        if (isLoggedIn()) {
            $user = currentUser();
            if ($user && $user['role'] === 'admin') {
                header("Location: index.php?page=admin_dashboard");
            } else {
                header("Location: index.php?page=dashboard");
            }
            exit();
        }
        require_once __DIR__ . '/../../views/auth/login.php';
    }

    public function processLogin() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: index.php?page=login");
            exit();
        }

        if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            $_SESSION['error'] = "Invalid session token. Please try submitting again.";
            header("Location: index.php?page=login");
            exit();
        }

        $login = trim($_POST['login'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($login) || empty($password)) {
            $_SESSION['error'] = "Please fill in all fields.";
            header("Location: index.php?page=login");
            exit();
        }

        try {
            $user = $this->userModel->authenticate($login, $password);
            if (!$user) {
                $_SESSION['error'] = "Invalid username/email or password.";
                header("Location: index.php?page=login");
                exit();
            }

            if ($user['status'] === 'suspended') {
                $_SESSION['error'] = "Your account is currently suspended by administration.";
                header("Location: index.php?page=login");
                exit();
            } elseif ($user['status'] === 'inactive') {
                $_SESSION['error'] = "Your account is currently inactive. Please complete USDT package activation or contact admin.";
                header("Location: index.php?page=login");
                exit();
            }

            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_role'] = $user['role'];

            if ($user['role'] === 'admin') {
                header("Location: index.php?page=admin_dashboard");
            } else {
                header("Location: index.php?page=dashboard");
            }
            exit();
        } catch (Exception $e) {
            $_SESSION['error'] = "Authentication Error: " . $e->getMessage();
            header("Location: index.php?page=login");
            exit();
        }
    }

    public function showRegister() {
        if (isLoggedIn()) {
            header("Location: index.php?page=dashboard");
            exit();
        }
        $refUsername = trim($_GET['ref'] ?? '');
        $sponsor = null;
        if (!empty($refUsername)) {
            try {
                $sponsor = $this->userModel->findByUsername($refUsername);
            } catch (Exception $e) {
                $sponsor = null;
            }
        }

        $pkgModel = new Package();
        $packages = $pkgModel->getAll();
        $settingsModel = new Settings();
        $trc20Address = $settingsModel->get('usdt_trc20_address', 'TYDzpSLtq8X2yB33x9v2QG2H1234567890');
        $bep20Address = $settingsModel->get('usdt_bep20_address', '0x572790c26FC062C247D0e1797D646F20a093AE14');
        $trc20Qr = $settingsModel->get('usdt_trc20_qr', 'public/assets/images/usdt_bep20_qr_clean.png');
        $bep20Qr = $settingsModel->get('usdt_bep20_qr', 'public/assets/images/usdt_bep20_qr_clean.png');
        $usdtRate = (float)$settingsModel->get('usdt_inr_rate', 90.00);

        require_once __DIR__ . '/../../views/auth/register.php';
    }

    public function processRegister() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: index.php?page=register");
            exit();
        }

        if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            $_SESSION['error'] = "Invalid session token. Please try submitting again.";
            header("Location: index.php?page=register");
            exit();
        }

        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $fullName = trim($_POST['full_name'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        $sponsorUsername = trim($_POST['sponsor'] ?? '');

        // USDT payment fields during registration
        $packageId = (int)($_POST['package_id'] ?? 1);
        $network = strtoupper(trim($_POST['network'] ?? 'TRC20'));
        $txid = trim($_POST['txid'] ?? '');

        if (empty($username) || empty($email) || empty($fullName) || empty($password)) {
            $_SESSION['error'] = "Required fields cannot be empty.";
            header("Location: index.php?page=register");
            exit();
        }

        if ($password !== $confirmPassword) {
            $_SESSION['error'] = "Passwords do not match.";
            header("Location: index.php?page=register");
            exit();
        }

        try {
            if ($this->userModel->findByUsername($username)) {
                $_SESSION['error'] = "Username is already taken.";
                header("Location: index.php?page=register");
                exit();
            }

            if ($this->userModel->findByEmail($email)) {
                $_SESSION['error'] = "Email address is already registered.";
                header("Location: index.php?page=register");
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

            // If a TXID was provided during registration, record the USDT deposit submission immediately
            if (!empty($txid)) {
                $pkgModel = new Package();
                $pkg = $pkgModel->getById($packageId) ?: $pkgModel->getById(1);
                $settingsModel = new Settings();
                $usdtRate = (float)$settingsModel->get('usdt_inr_rate', 90.00);
                if ($usdtRate <= 0) $usdtRate = 90.00;

                $amountInr = (float)$pkg['price'];
                $amountUsdt = round($amountInr / $usdtRate, 2);

                $walletAddress = ($network === 'BEP20')
                    ? $settingsModel->get('usdt_bep20_address', '0x572790c26FC062C247D0e1797D646F20a093AE14')
                    : $settingsModel->get('usdt_trc20_address', 'TYDzpSLtq8X2yB33x9v2QG2H1234567890');

                $usdtModel = new UsdtPayment();
                $usdtModel->createPayment($newUserId, (int)$pkg['id'], $amountInr, $amountUsdt, $network, $walletAddress, $txid);

                $_SESSION['success'] = "Registration completed and USDT payment proof submitted! Your activation is currently queued for admin approval.";
                header("Location: index.php?page=usdt_activate");
                exit();
            }

            $_SESSION['success'] = "Registration successful! You may now submit your USDT payment proof to activate your package.";
            header("Location: index.php?page=usdt_activate");
            exit();
        } catch (Exception $e) {
            $_SESSION['error'] = "Registration Error: " . $e->getMessage();
            header("Location: index.php?page=register");
            exit();
        }
    }

    public function logout() {
        session_destroy();
        header("Location: index.php?page=login");
        exit();
    }
}
