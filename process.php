<?php
/**
 * ============================================================
 * process.php
 * Student Technology and Innovation Conference
 * Admission Number: 23510
 * Individualization: 10% discount when attendance is exactly 3 days.
 *
 * This script:
 *   1. Receives form data via POST
 *   2. Independently validates all inputs (server-side)
 *   3. Independently calculates all fees and discount
 *   4. Displays a confirmation page or an error page
 * ============================================================
 */

// ============================================================
// 1. FEE CONSTANTS (must match JavaScript exactly)
// ============================================================
define('REGISTRATION_FEE', 1000);
define('ATTENDANCE_RATE', 500);    // per day
define('WORKSHOP_FEE', 750);
define('MEAL_RATE', 300);          // per day

// ============================================================
// 2. INITIALIZE VARIABLES
// ============================================================
$errors = array();
$success = false;

// Default values
$full_name        = '';
$admission_number = '';
$email            = '';
$phone            = '';
$year             = '';
$category         = '';
$days             = '';
$workshop         = '';
$meal             = '';

// ============================================================
// 3. CHECK REQUEST METHOD
// ============================================================
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    // If someone visits process.php directly, redirect to register page
    header('Location: register.html');
    exit;
}

// ============================================================
// 4. RETRIEVE AND TRIM INPUT
// ============================================================
$full_name        = trim($_POST['full_name'] ?? '');
$admission_number = trim($_POST['admission_number'] ?? '');
$email            = trim($_POST['email'] ?? '');
$phone            = trim($_POST['phone'] ?? '');
$year             = trim($_POST['year'] ?? '');
$category         = trim($_POST['category'] ?? '');
$days             = trim($_POST['days'] ?? '');
$workshop         = trim($_POST['workshop'] ?? '');
$meal             = trim($_POST['meal'] ?? '');

// ============================================================
// 5. SERVER-SIDE VALIDATION
// ============================================================

// ----- Full Name -----
if ($full_name === '') {
    $errors[] = 'Full name is required.';
} elseif (strlen($full_name) < 3) {
    $errors[] = 'Full name must be at least 3 characters long.';
}

// ----- Admission Number -----
if ($admission_number === '') {
    $errors[] = 'Admission number is required.';
}

// ----- Email -----
if ($email === '') {
    $errors[] = 'Email address is required.';
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Please enter a valid email address.';
}

// ----- Phone: exactly 10 digits -----
if ($phone === '') {
    $errors[] = 'Phone number is required.';
} elseif (!preg_match('/^\d{10}$/', $phone)) {
    $errors[] = 'Phone number must be exactly 10 digits.';
}

// ----- Year of Study -----
$valid_years = array('Year 1', 'Year 2', 'Year 3', 'Year 4');
if ($year === '') {
    $errors[] = 'Year of study is required.';
} elseif (!in_array($year, $valid_years, true)) {
    $errors[] = 'Invalid year of study selected.';
}

// ----- Participation Category -----
$valid_categories = array('Conference Only', 'Workshop Participant');
if ($category === '') {
    $errors[] = 'Participation category is required.';
} elseif (!in_array($category, $valid_categories, true)) {
    $errors[] = 'Invalid participation category selected.';
}

// ----- Conference Days -----
$days_int = 0;
if ($days === '') {
    $errors[] = 'Number of conference days is required.';
} else {
    $days_int = intval($days);
    if ($days_int < 1 || $days_int > 3) {
        $errors[] = 'Number of conference days must be between 1 and 3.';
    }
}

// ----- Workshop -----
$valid_workshops = array(
    'Web Development',
    'Artificial Intelligence',
    'Cybersecurity',
    'Mobile Application Development'
);

if ($category === 'Workshop Participant') {
    // Workshop is required
    if ($workshop === '') {
        $errors[] = 'Please select a workshop.';
    } elseif (!in_array($workshop, $valid_workshops, true)) {
        $errors[] = 'Invalid workshop selection.';
    }
} else {
    // Conference Only: ignore any workshop value
    $workshop = '';
}

// ----- Meal Option -----
$valid_meals = array('No Lunch', 'Lunch');
if ($meal === '') {
    $errors[] = 'Meal option is required.';
} elseif (!in_array($meal, $valid_meals, true)) {
    $errors[] = 'Invalid meal option selected.';
}

// ============================================================
// 6. IF NO ERRORS, CALCULATE FEES ON SERVER SIDE
// ============================================================
if (empty($errors)) {
    $success = true;

    // --- Individual Fees ---
    $registration_fee = REGISTRATION_FEE;
    $attendance_fee   = $days_int * ATTENDANCE_RATE;

    $workshop_fee = 0;
    if ($category === 'Workshop Participant' && $workshop !== '') {
        $workshop_fee = WORKSHOP_FEE;
    }

    $meal_fee = 0;
    if ($meal === 'Lunch') {
        $meal_fee = $days_int * MEAL_RATE;
    }

    // --- Subtotal ---
    $subtotal = $registration_fee + $attendance_fee + $workshop_fee + $meal_fee;

    // --- Discount: 10% only when days == 3 ---
    $discount = 0;
    if ($days_int === 3) {
        $discount = $subtotal * 0.10;
    }

    // --- Final Total ---
    $total = $subtotal - $discount;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
        <?php echo $success ? 'Registration Confirmation' : 'Registration Error'; ?>
        - STIC 2026
    </title>
    <style>
        /* ========== RESET & BASE ========== */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f6f9;
            color: #1e2a3a;
            line-height: 1.6;
        }

        /* ========== NAVBAR ========== */
        .navbar {
            background-color: #0a2540;
            padding: 1rem 0;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .nav-container {
            max-width: 1100px;
            margin: 0 auto;
            padding: 0 1.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .nav-brand {
            color: #c9a227;
            font-size: 1.4rem;
            font-weight: 700;
            letter-spacing: 1px;
        }

        .nav-links {
            list-style: none;
            display: flex;
            gap: 2rem;
        }

        .nav-links a {
            color: #ffffff;
            text-decoration: none;
            font-weight: 500;
            padding-bottom: 4px;
            border-bottom: 2px solid transparent;
            transition: border-color 0.2s, color 0.2s;
        }

        .nav-links a:hover {
            border-bottom-color: #c9a227;
            color: #c9a227;
        }

        /* ========== PAGE HEADER ========== */
        .page-header {
            background: linear-gradient(135deg, #0a2540 0%, #1a3a5c 100%);
            color: #ffffff;
            padding: 3rem 1.5rem;
            text-align: center;
        }

        .page-header h1 {
            font-size: 2.2rem;
            margin-bottom: 0.5rem;
        }

        .page-header p {
            font-size: 1.05rem;
            color: #f0c75e;
            font-weight: 500;
        }

        /* ========== CONTAINER ========== */
        .container {
            max-width: 900px;
            margin: 0 auto;
            padding: 2.5rem 1.5rem;
        }

        /* ========== SUCCESS BOX ========== */
        .confirmation-box {
            background: #ffffff;
            border: 1px solid #cbd5e1;
            border-radius: 10px;
            padding: 2rem;
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.06);
        }

        .confirmation-box .status-title {
            color: #15803d;
            font-size: 2rem;
            text-align: center;
            margin-bottom: 0.5rem;
            letter-spacing: 0.5px;
        }

        .confirmation-box .subhead {
            text-align: center;
            color: #475569;
            margin-bottom: 2rem;
            font-size: 1rem;
        }

        .section-title {
            color: #0a2540;
            font-size: 1.3rem;
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 1px solid #cbd5e1;
        }

        /* ========== DETAIL TABLE ========== */
        .detail-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 2rem;
        }

        .detail-table th,
        .detail-table td {
            text-align: left;
            padding: 0.75rem 0.6rem;
            border-bottom: 1px solid #e2e8f0;
            font-size: 0.98rem;
        }

        .detail-table th {
            width: 40%;
            color: #0a2540;
            font-weight: 700;
            background: #f1f5f9;
        }

        .detail-table td {
            color: #334155;
        }

        /* ========== FEE BREAKDOWN ========== */
        .fee-breakdown {
            background: #eef2f7;
            padding: 1.5rem;
            border-radius: 8px;
            border-left: 4px solid #c9a227;
            margin-bottom: 2rem;
        }

        .fee-breakdown h3 {
            color: #0a2540;
            margin-bottom: 1rem;
            font-size: 1.2rem;
        }

        .fee-row {
            display: flex;
            justify-content: space-between;
            padding: 0.5rem 0;
            font-size: 1rem;
            border-bottom: 1px dashed #d1d5db;
        }

        .fee-row:last-child {
            border-bottom: none;
        }

        .subtotal-row {
            font-weight: 700;
            color: #0a2540;
            border-top: 2px solid #0a2540;
            border-bottom: none;
            margin-top: 0.4rem;
            padding-top: 0.7rem;
        }

        .discount-row {
            color: #15803d;
            font-weight: 700;
            border-bottom: none;
        }

        .total-row {
            font-size: 1.15rem;
            font-weight: 800;
            color: #0a2540;
            border-top: 3px solid #c9a227;
            border-bottom: none;
            margin-top: 0.5rem;
            padding-top: 0.8rem;
        }

        .discount-note {
            color: #15803d;
            font-weight: 700;
            margin-top: 0.8rem;
            font-size: 0.95rem;
            padding: 0.6rem 0.9rem;
            background: #f0fdf4;
            border-radius: 5px;
            border-left: 3px solid #15803d;
        }

        .no-discount-note {
            color: #64748b;
            font-weight: 500;
            margin-top: 0.8rem;
            font-size: 0.9rem;
            font-style: italic;
        }

        /* ========== THANK YOU ========== */
        .thank-you {
            text-align: center;
            font-size: 1.05rem;
            color: #0a2540;
            font-weight: 600;
            margin-bottom: 1.5rem;
            padding: 1rem;
            background: #f8fafc;
            border-radius: 6px;
        }

        /* ========== ERROR BOX ========== */
        .error-box {
            background: #fef2f2;
            border: 2px solid #b91c1c;
            border-radius: 10px;
            padding: 2rem;
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.06);
        }

        .error-box h1 {
            color: #b91c1c;
            font-size: 1.8rem;
            margin-bottom: 1rem;
            text-align: center;
        }

        .error-box > p {
            color: #7f1d1d;
            margin-bottom: 1rem;
            text-align: center;
        }

        .error-box ul {
            margin: 0 0 1.5rem 1.5rem;
            color: #7f1d1d;
        }

        .error-box ul li {
            margin-bottom: 0.4rem;
            font-size: 0.98rem;
        }

        /* ========== BUTTONS ========== */
        .btn {
            display: inline-block;
            padding: 0.75rem 1.9rem;
            border: none;
            border-radius: 6px;
            font-size: 1rem;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            transition: background-color 0.2s, transform 0.1s, box-shadow 0.2s;
            text-align: center;
        }

        .btn:active { transform: scale(0.98); }

        .btn-primary {
            background-color: #c9a227;
            color: #0a2540;
            box-shadow: 0 4px 14px rgba(201, 162, 39, 0.4);
        }

        .btn-primary:hover {
            background-color: #b8911f;
            box-shadow: 0 6px 18px rgba(201, 162, 39, 0.55);
        }

        .btn-secondary {
            background-color: transparent;
            color: #0a2540;
            border: 2px solid #0a2540;
        }

        .btn-secondary:hover {
            background-color: #0a2540;
            color: #ffffff;
        }

        .btn-large {
            padding: 0.95rem 2.4rem;
            font-size: 1.05rem;
        }

        .action-buttons {
            display: flex;
            justify-content: center;
            gap: 1rem;
            flex-wrap: wrap;
            margin-top: 1.5rem;
            padding-top: 1.5rem;
            border-top: 1px solid #cbd5e1;
        }

        /* ========== FOOTER ========== */
        .footer {
            background: #0a2540;
            color: #ffffff;
            text-align: center;
            padding: 1.5rem;
            margin-top: 3rem;
            font-size: 0.9rem;
        }

        /* ========== RESPONSIVE ========== */
        @media (max-width: 700px) {
            .nav-container {
                flex-direction: column;
                gap: 0.8rem;
            }

            .page-header h1 { font-size: 1.6rem; }

            .page-header p { font-size: 0.95rem; }

            .confirmation-box .status-title { font-size: 1.5rem; }

            .detail-table th,
            .detail-table td {
                font-size: 0.9rem;
                padding: 0.55rem 0.4rem;
            }

            .action-buttons {
                flex-direction: column;
            }

            .action-buttons .btn {
                width: 100%;
            }

            .fee-row {
                font-size: 0.92rem;
            }

            .total-row {
                font-size: 1.02rem;
            }
        }
    </style>
</head>
<body>

    <!-- ============================= -->
    <!-- NAVIGATION BAR                -->
    <!-- ============================= -->
    <nav class="navbar">
        <div class="nav-container">
            <div class="nav-brand">STIC 2026</div>
            <ul class="nav-links">
                <li><a href="index.html">Home</a></li>
                <li><a href="register.html">Register</a></li>
            </ul>
        </div>
    </nav>

    <!-- ============================= -->
    <!-- PAGE HEADER                   -->
    <!-- ============================= -->
    <header class="page-header">
        <h1><?php echo $success ? 'Registration Confirmation' : 'Registration Error'; ?></h1>
        <p>Student Technology and Innovation Conference &nbsp;|&nbsp; 15th &ndash; 17th November 2026</p>
    </header>

    <!-- ============================= -->
    <!-- MAIN CONTENT                  -->
    <!-- ============================= -->
    <main class="container">

        <?php if ($success): ?>

            <!-- ============================================= -->
            <!-- SUCCESSFUL REGISTRATION                       -->
            <!-- ============================================= -->
            <div class="confirmation-box">
                <h1 class="status-title">REGISTRATION SUCCESSFUL</h1>
                <p class="subhead">
                    Your registration for the Student Technology and Innovation
                    Conference has been received and processed.
                </p>

                <!-- Participant Details -->
                <h2 class="section-title">Participant Details</h2>
                <table class="detail-table">
                    <tr>
                        <th>Participant:</th>
                        <td><?php echo htmlspecialchars($full_name); ?></td>
                    </tr>
                    <tr>
                        <th>Admission Number:</th>
                        <td><?php echo htmlspecialchars($admission_number); ?></td>
                    </tr>
                    <tr>
                        <th>Email:</th>
                        <td><?php echo htmlspecialchars($email); ?></td>
                    </tr>
                    <tr>
                        <th>Phone:</th>
                        <td><?php echo htmlspecialchars($phone); ?></td>
                    </tr>
                    <tr>
                        <th>Year of Study:</th>
                        <td><?php echo htmlspecialchars($year); ?></td>
                    </tr>
                    <tr>
                        <th>Participation Category:</th>
                        <td><?php echo htmlspecialchars($category); ?></td>
                    </tr>
                    <tr>
                        <th>Conference Days:</th>
                        <td><?php echo htmlspecialchars($days_int); ?> day(s)</td>
                    </tr>
                    <tr>
                        <th>Workshop:</th>
                        <td>
                            <?php
                            if ($workshop !== '') {
                                echo htmlspecialchars($workshop);
                            } else {
                                echo 'None (Conference Only)';
                            }
                            ?>
                        </td>
                    </tr>
                    <tr>
                        <th>Meals:</th>
                        <td><?php echo htmlspecialchars($meal); ?></td>
                    </tr>
                </table>

                <!-- Fee Breakdown -->
                <h2 class="section-title">Fee Breakdown</h2>
                <div class="fee-breakdown">
                    <div class="fee-row">
                        <span>Registration Fee:</span>
                        <span>KSh <?php echo number_format($registration_fee, 2); ?></span>
                    </div>
                    <div class="fee-row">
                        <span>Attendance Fee (<?php echo $days_int; ?> &times; KSh 500):</span>
                        <span>KSh <?php echo number_format($attendance_fee, 2); ?></span>
                    </div>
                    <div class="fee-row">
                        <span>Workshop Fee:</span>
                        <span>KSh <?php echo number_format($workshop_fee, 2); ?></span>
                    </div>
                    <div class="fee-row">
                        <span>Meal Fee:</span>
                        <span>KSh <?php echo number_format($meal_fee, 2); ?></span>
                    </div>
                    <div class="fee-row subtotal-row">
                        <span>Subtotal:</span>
                        <span>KSh <?php echo number_format($subtotal, 2); ?></span>
                    </div>
                    <div class="fee-row discount-row">
                        <span>Discount (10% for 3 days):</span>
                        <span>KSh <?php echo number_format($discount, 2); ?></span>
                    </div>
                    <div class="fee-row total-row">
                        <span>TOTAL AMOUNT PAYABLE:</span>
                        <span>KSh <?php echo number_format($total, 2); ?></span>
                    </div>

                    <?php if ($days_int === 3): ?>
                        <p class="discount-note">
                            10% Three-Day Attendance Discount Applied
                        </p>
                    <?php else: ?>
                        <p class="no-discount-note">
                            Discount: KSh 0 (The 10% discount is only applicable for
                            3-day attendance.)
                        </p>
                    <?php endif; ?>
                </div>

                <!-- Thank you and actions -->
                <p class="thank-you">
                    Thank you for registering for the Student Technology and Innovation Conference.
                </p>

                <div class="action-buttons">
                    <a href="register.html" class="btn btn-primary btn-large">Return to Registration</a>
                    <a href="index.html" class="btn btn-secondary btn-large">Back Home</a>
                </div>
            </div>

        <?php else: ?>

            <!-- ============================================= -->
            <!-- VALIDATION ERRORS                             -->
            <!-- ============================================= -->
            <div class="error-box">
                <h1>Registration Failed</h1>
                <p>The following issues were found in your submission. Please correct them and try again.</p>
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>

                <div class="action-buttons">
                    <a href="register.html" class="btn btn-primary btn-large">Return to Registration Form</a>
                    <a href="index.html" class="btn btn-secondary btn-large">Back Home</a>
                </div>
            </div>

        <?php endif; ?>

    </main>

    <!-- ============================= -->
    <!-- FOOTER                        -->
    <!-- ============================= -->
    <footer class="footer">
        <p>&copy; 2026 Student Technology and Innovation Conference. All rights reserved.</p>
    </footer>

</body>
</html>