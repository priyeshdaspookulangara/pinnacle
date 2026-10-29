<?php
// src/Services/PdfService.php - Pinnacle Hunt PDF Brochure Generator

require_once __DIR__ . '/FPDF.php';
require_once __DIR__ . '/../Models/Package.php';
require_once __DIR__ . '/../Models/Settings.php';

class PinnacleBrochurePdf extends FPDF
{
    protected $userName = '';
    protected $userRefUrl = '';

    public function setUserDetails(string $userName, string $userRefUrl)
    {
        $this->userName = $userName;
        $this->userRefUrl = $userRefUrl;
    }

    public function Header()
    {
        // Dark theme header bar
        $this->SetFillColor(11, 15, 25); // Dark background #0b0f19
        $this->Rect(0, 0, 210, 28, 'F');

        // Brand logo icon box
        $this->SetFillColor(34, 197, 94); // Emerald #22c55e
        $this->Rect(12, 5, 16, 16, 'F');
        $this->SetFont('Helvetica', 'B', 14);
        $this->SetTextColor(0, 0, 0);
        $this->SetXY(12, 5);
        $this->Cell(16, 16, 'P', 0, 0, 'C');

        // Brand Title
        $this->SetFont('Helvetica', 'B', 16);
        $this->SetTextColor(255, 255, 255);
        $this->SetXY(32, 6);
        $this->Cell(100, 8, 'PINNACLE HUNT', 0, 0, 'L');

        // Subtitle
        $this->SetFont('Helvetica', '', 8);
        $this->SetTextColor(74, 222, 128); // emerald-400
        $this->SetXY(32, 14);
        $this->Cell(100, 5, 'High-Performance BFS 3-Matrix Forced Spillover Platform', 0, 0, 'L');

        // Document Badge
        $this->SetFillColor(22, 163, 74);
        $this->SetTextColor(255, 255, 255);
        $this->SetFont('Helvetica', 'B', 8);
        $this->SetXY(145, 8);
        $this->Cell(53, 10, 'OFFICIAL BUSINESS PLAN', 0, 0, 'C', true);

        // Header Divider Line
        $this->SetDrawColor(34, 197, 94);
        $this->SetLineWidth(0.8);
        $this->Line(0, 28, 210, 28);

        $this->Ln(12);
    }

    public function Footer()
    {
        $this->SetY(-18);
        $this->SetDrawColor(31, 41, 55);
        $this->SetLineWidth(0.3);
        $this->Line(12, 280, 198, 280);

        $this->SetFont('Helvetica', '', 8);
        $this->SetTextColor(107, 114, 128);
        $this->SetXY(12, 282);
        $this->Cell(100, 5, 'Pinnacle Hunt (c) ' . date('Y') . ' | Confidential Business Plan Brochure', 0, 0, 'L');

        $this->SetXY(150, 282);
        $this->Cell(48, 5, 'Page ' . $this->PageNo() . ' of {nb}', 0, 0, 'R');
    }
}

class PdfService
{
    public function generateBusinessPlanBrochure(?array $user = null): string
    {
        $packageModel = new Package();
        $packages = $packageModel->getAllActive();

        $settingsModel = new Settings();
        $settings = $settingsModel->getAll();

        $pdf = new PinnacleBrochurePdf('P', 'mm', 'A4');
        $pdf->AliasNbPages();

        $userName = $user['full_name'] ?? ($user['username'] ?? 'Pinnacle Partner');
        $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? "https" : "http";
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost:8000';
        $refCode = $user['username'] ?? 'admin';
        $userRefUrl = "{$protocol}://{$host}/index.php?page=register&ref=" . urlencode($refCode);

        $pdf->setUserDetails($userName, $userRefUrl);
        $pdf->SetTitle('Pinnacle Hunt - Official Business Plan Brochure');
        $pdf->SetAuthor('Pinnacle Hunt Platform');

        // Page 1
        $pdf->AddPage();
        $pdf->SetAutoPageBreak(true, 20);

        // Hero Banner Box
        $pdf->SetFillColor(17, 24, 39); // darkcard
        $pdf->Rect(12, 32, 186, 28, 'F');
        $pdf->SetDrawColor(34, 197, 94);
        $pdf->SetLineWidth(0.5);
        $pdf->Rect(12, 32, 186, 28, 'D');

        $pdf->SetXY(16, 35);
        $pdf->SetFont('Helvetica', 'B', 13);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->Cell(178, 6, 'AUTOMATED BFS 3-MATRIX WEALTH GENERATION SYSTEM', 0, 1, 'L');

        $pdf->SetX(16);
        $pdf->SetFont('Helvetica', '', 8.5);
        $pdf->SetTextColor(156, 163, 175);
        $pdf->MultiCell(178, 4, "Pinnacle Hunt is a revolutionary financial matrix ecosystem powered by Breadth-First Search (BFS) spillover technology, 10% direct sponsor commissions, 6-level matrix rewards, and an automated 25% reserve upgrade engine.", 0, 'L');

        $pdf->Ln(6);

        // Core Platform Highlights Grid (3 Columns)
        $pdf->SetFont('Helvetica', 'B', 10);
        $pdf->SetTextColor(34, 197, 94);
        $pdf->Cell(186, 6, 'KEY PLATFORM PILLARS', 0, 1, 'L');

        $pdf->SetFont('Helvetica', '', 8);

        // Box 1: Direct Referral
        $pdf->SetFillColor(243, 244, 246);
        $pdf->Rect(12, 73, 58, 22, 'F');
        $pdf->SetXY(14, 75);
        $pdf->SetFont('Helvetica', 'B', 8.5);
        $pdf->SetTextColor(16, 185, 129);
        $pdf->Cell(54, 4, '10% Direct Sponsor', 0, 1, 'L');
        $pdf->SetX(14);
        $pdf->SetFont('Helvetica', '', 7.5);
        $pdf->SetTextColor(55, 65, 81);
        $pdf->MultiCell(54, 3.5, "Earn 10% instant cash direct commission on every package purchase made by your personal invitees.", 0, 'L');

        // Box 2: BFS Forced Spillover
        $pdf->Rect(76, 73, 58, 22, 'F');
        $pdf->SetXY(78, 75);
        $pdf->SetFont('Helvetica', 'B', 8.5);
        $pdf->SetTextColor(37, 99, 235);
        $pdf->Cell(54, 4, 'BFS Forced Spillover', 0, 1, 'L');
        $pdf->SetX(78);
        $pdf->SetFont('Helvetica', '', 7.5);
        $pdf->SetTextColor(55, 65, 81);
        $pdf->MultiCell(54, 3.5, "Company-wide Breadth-First Search places new entries systematically, filling matrix gaps across 6 depth levels.", 0, 'L');

        // Box 3: Auto-Upgrade Pool
        $pdf->Rect(140, 73, 58, 22, 'F');
        $pdf->SetXY(142, 75);
        $pdf->SetFont('Helvetica', 'B', 8.5);
        $pdf->SetTextColor(217, 119, 6);
        $pdf->Cell(54, 4, '25% Reserve Upgrade', 0, 1, 'L');
        $pdf->SetX(142);
        $pdf->SetFont('Helvetica', '', 7.5);
        $pdf->SetTextColor(55, 65, 81);
        $pdf->MultiCell(54, 3.5, "25% of gross earnings automatically accumulates to auto-upgrade your account to higher package tiers.", 0, 'L');

        $pdf->SetY(100);

        // Section: Package Tiers Table
        $pdf->SetFont('Helvetica', 'B', 10);
        $pdf->SetTextColor(11, 15, 25);
        $pdf->Cell(186, 6, '1. INVESTMENT & PACKAGE TIERS', 0, 1, 'L');

        // Table Header
        $pdf->SetFillColor(11, 15, 25);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->SetFont('Helvetica', 'B', 8);

        $pdf->Cell(20, 7, 'Tier', 1, 0, 'C', true);
        $pdf->Cell(50, 7, 'Package Name', 1, 0, 'L', true);
        $pdf->Cell(35, 7, 'Price (INR)', 1, 0, 'R', true);
        $pdf->Cell(40, 7, 'Direct Sponsor (10%)', 1, 0, 'R', true);
        $pdf->Cell(41, 7, 'Matrix Tree Tier', 1, 1, 'C', true);

        // Table Rows
        $pdf->SetFont('Helvetica', '', 8);
        $pdf->SetTextColor(31, 41, 55);

        $fill = false;
        foreach ($packages as $pkg) {
            $pdf->SetFillColor($fill ? 249 : 255, $fill ? 250 : 255, $fill ? 251 : 255);

            $directAmt = ($pkg['price'] * $pkg['direct_commission_percent']) / 100;

            $pdf->Cell(20, 6, 'Tier #' . $pkg['sort_order'], 1, 0, 'C', true);
            $pdf->Cell(50, 6, $pkg['name'], 1, 0, 'L', true);
            $pdf->Cell(35, 6, 'Rs. ' . number_format($pkg['price'], 2), 1, 0, 'R', true);
            $pdf->Cell(40, 6, 'Rs. ' . number_format($directAmt, 2), 1, 0, 'R', true);
            $pdf->Cell(41, 6, '3-Matrix (Levels 1-6)', 1, 1, 'C', true);

            $fill = !$fill;
        }

        $pdf->Ln(5);

        // Page 2: Compensation Plan Details & Spillover Mechanics
        $pdf->AddPage();

        $pdf->SetFont('Helvetica', 'B', 10);
        $pdf->SetTextColor(11, 15, 25);
        $pdf->Cell(186, 6, '2. MATRIX LEVEL PAYOUT STRUCTURE (6-LEVEL DEPTH)', 0, 1, 'L');

        // Level payout table
        $pdf->SetFillColor(16, 185, 129);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->SetFont('Helvetica', 'B', 8);

        $pdf->Cell(25, 7, 'Matrix Level', 1, 0, 'C', true);
        $pdf->Cell(35, 7, 'Max Members', 1, 0, 'C', true);
        $pdf->Cell(40, 7, 'Payout %', 1, 0, 'C', true);
        $pdf->Cell(86, 7, 'Example Payout on Tier 1 (Rs. 500)', 1, 1, 'L', true);

        $levels = [
            1 => ['members' => 3, 'percent' => $settings['matrix_level_1_percent'] ?? 5.0],
            2 => ['members' => 9, 'percent' => $settings['matrix_level_2_percent'] ?? 4.0],
            3 => ['members' => 27, 'percent' => $settings['matrix_level_3_percent'] ?? 3.0],
            4 => ['members' => 81, 'percent' => $settings['matrix_level_4_percent'] ?? 2.0],
            5 => ['members' => 243, 'percent' => $settings['matrix_level_5_percent'] ?? 1.0],
            6 => ['members' => 729, 'percent' => $settings['matrix_level_6_percent'] ?? 1.0],
        ];

        $pdf->SetFont('Helvetica', '', 8);
        $pdf->SetTextColor(31, 41, 55);

        $fill = false;
        $totalMembers = 0;
        foreach ($levels as $lvl => $data) {
            $pdf->SetFillColor($fill ? 243 : 255, $fill ? 244 : 255, $fill ? 246 : 255);
            $totalMembers += $data['members'];
            $payoutPerMember = (500 * $data['percent']) / 100;
            $maxLevelPayout = $payoutPerMember * $data['members'];

            $pdf->Cell(25, 6, 'Level ' . $lvl, 1, 0, 'C', true);
            $pdf->Cell(35, 6, number_format($data['members']), 1, 0, 'C', true);
            $pdf->Cell(40, 6, number_format($data['percent'], 1) . '%', 1, 0, 'C', true);
            $pdf->Cell(86, 6, 'Rs. ' . number_format($payoutPerMember, 2) . ' / member (Max: Rs. ' . number_format($maxLevelPayout, 2) . ')', 1, 1, 'L', true);

            $fill = !$fill;
        }

        // Summary row
        $pdf->SetFont('Helvetica', 'B', 8);
        $pdf->SetFillColor(229, 231, 235);
        $pdf->Cell(25, 6, 'TOTAL', 1, 0, 'C', true);
        $pdf->Cell(35, 6, number_format($totalMembers) . ' Members', 1, 0, 'C', true);
        $pdf->Cell(40, 6, '16.0% Combined', 1, 0, 'C', true);
        $pdf->Cell(86, 6, 'Comprehensive Level Residual Stream Across All Packages', 1, 1, 'L', true);

        $pdf->Ln(6);

        // Section: How BFS Forced Spillover Works
        $pdf->SetFont('Helvetica', 'B', 10);
        $pdf->SetTextColor(11, 15, 25);
        $pdf->Cell(186, 6, '3. BREADTH-FIRST SEARCH (BFS) FORCED SPILLOVER MECHANICS', 0, 1, 'L');

        $pdf->SetFont('Helvetica', '', 8);
        $pdf->SetTextColor(55, 65, 81);
        $pdf->MultiCell(186, 4, "Unlike traditional unlevel MLMs where downlines remain imbalanced, Pinnacle Hunt uses a Breadth-First Search (BFS) algorithm to place members systematically row-by-row, level-by-level.\n\n" .
            "- Position Capacity: Each matrix node accommodates exactly 3 direct child nodes.\n" .
            "- Automated Spillover: When a parent's 3 positions are filled, all subsequent members sponsored by that node automatically spill over to fill the next available position in the queue.\n" .
            "- Isolated Package Trees: Every package tier (₹500 to ₹1,00,000) maintains its own matrix tree structure, allowing members to earn simultaneous stream commissions across active tiers.", 0, 'L');

        $pdf->Ln(6);

        // Section: 25% Reserve Upgrade Pool Workflow
        $pdf->SetFont('Helvetica', 'B', 10);
        $pdf->SetTextColor(11, 15, 25);
        $pdf->Cell(186, 6, '4. AUTOMATED 25% RESERVE UPGRADE POOL & TIER PROGRESSION', 0, 1, 'L');

        $pdf->SetFont('Helvetica', '', 8);
        $pdf->SetTextColor(55, 65, 81);
        $pdf->MultiCell(186, 4, "To ensure continuous upward movement and higher commission payouts for all members:\n" .
            "1. 25% Holdback: 25% of all incoming commissions are safely allocated to your Reserve Pool, while 75% is immediately withdrawable.\n" .
            "2. Automated Package Unlock: When your Reserve Pool reaches the cost of the next package tier (e.g. ₹1,000 for Tier 2), the system automatically activates the tier and places you in the new matrix tree without out-of-pocket costs!", 0, 'L');

        $pdf->Ln(6);

        // Call to Action Box for Member / Partner
        $pdf->SetFillColor(11, 15, 25);
        $pdf->Rect(12, $pdf->GetY(), 186, 32, 'F');

        $pdf->SetXY(16, $pdf->GetY() + 3);
        $pdf->SetFont('Helvetica', 'B', 11);
        $pdf->SetTextColor(34, 197, 94);
        $pdf->Cell(178, 5, 'READY TO START YOUR PINNACLE HUNT JOURNEY?', 0, 1, 'L');

        $pdf->SetX(16);
        $pdf->SetFont('Helvetica', 'B', 8.5);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->Cell(178, 4, 'Invited By: ' . $userName, 0, 1, 'L');

        $pdf->SetX(16);
        $pdf->SetFont('Helvetica', '', 8);
        $pdf->SetTextColor(209, 213, 219);
        $pdf->Cell(178, 4, 'Join Link: ' . $userRefUrl, 0, 1, 'L');

        $pdf->SetX(16);
        $pdf->SetFont('Helvetica', 'I', 7.5);
        $pdf->SetTextColor(156, 163, 175);
        $pdf->Cell(178, 4, 'Register online to activate Tier 1 (Rs. 500) and unlock instant commission streams!', 0, 1, 'L');

        return $pdf->Output('S', 'Pinnacle_Hunt_Business_Plan.pdf');
    }
}
