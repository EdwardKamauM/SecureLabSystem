<?php
require_once "../includes/auth.php";
require_once "../config/db.php"; // This must define $pdo as your PDO connection

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// --- Fetch summary stats ---
$totalUsers = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$totalComputers = $pdo->query("SELECT COUNT(*) FROM computers")->fetchColumn();

$availableComputersStmt = $pdo->prepare("SELECT COUNT(*) FROM computers WHERE status = ?");
$availableComputersStmt->execute(['available']);
$availableComputers = $availableComputersStmt->fetchColumn();

$atRiskStmt = $pdo->prepare("SELECT COUNT(*) FROM computers WHERE maintenance_risk = ?");
$atRiskStmt->execute([1]); // 1 means high risk
$atRisk = $atRiskStmt->fetchColumn();

// --- Fetch recent bookings (10 latest) ---
$bookingsQuery = "
    SELECT b.*, u.name AS user_name, c.label AS computer_label 
    FROM bookings b 
    JOIN users u ON b.user_id = u.id 
    JOIN computers c ON b.computer_id = c.id 
    ORDER BY b.created_at DESC 
    LIMIT 10
";
$recentBookings = $pdo->query($bookingsQuery)->fetchAll(PDO::FETCH_ASSOC);

// --- Search/Report Logic ---
$reportType = $_GET['report_type'] ?? '';
$searchResults = [];

if ($reportType === 'rejected_users') {
    // Fetch rejected users
    $stmt = $pdo->prepare("SELECT id, name, email, status FROM users WHERE status = ?");
    $stmt->execute(['rejected']);
    $searchResults = $stmt->fetchAll(PDO::FETCH_ASSOC);
} elseif ($reportType === 'high_risk_computers') {
    // Fetch high risk computers
    $stmt = $pdo->prepare("SELECT id, label, status, maintenance_risk FROM computers WHERE maintenance_risk = ?");
    $stmt->execute([1]);
    $searchResults = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// --- Export Logic for filtered reports and recent bookings ---
if (isset($_GET['format'])) {
    $format = $_GET['format'];
    $exportReportType = $_GET['report_type'] ?? '';

    // Determine data and headers based on report_type
    if ($exportReportType === 'rejected_users') {
        $stmt = $pdo->prepare("SELECT id, name, email, status FROM users WHERE status = ?");
        $stmt->execute(['rejected']);
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $headers = ['ID', 'Name', 'Email', 'Status'];
        $filename = 'rejected_users_report.' . $format;
    } elseif ($exportReportType === 'high_risk_computers') {
        $stmt = $pdo->prepare("SELECT id, label, status FROM computers WHERE maintenance_risk = ?");
        $stmt->execute([1]);
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $headers = ['ID', 'Label', 'Status'];
        $filename = 'high_risk_computers_report.' . $format;
    } else {
        // Default export recent bookings
        $data = $recentBookings;
        $headers = ['User', 'Computer', 'Status', 'Created At'];
        $filename = 'system_report.' . $format;
    }

    // Export CSV
    if ($format === 'csv') {
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        $output = fopen("php://output", "w");
        fputcsv($output, $headers);
        foreach ($data as $row) {
            $line = [];
            foreach ($headers as $col) {
                $key = strtolower(str_replace(' ', '_', $col));
                $line[] = $row[$key] ?? '';
            }
            fputcsv($output, $line);
        }
        fclose($output);
        exit;
    }

    // Export XLSX
    if ($format === 'xlsx') {
        require '../vendor/autoload.php';
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->fromArray($headers, NULL, 'A1');
        $rowNum = 2;
        foreach ($data as $row) {
            $line = [];
            foreach ($headers as $col) {
                $key = strtolower(str_replace(' ', '_', $col));
                $line[] = $row[$key] ?? '';
            }
            $sheet->fromArray($line, NULL, 'A' . $rowNum++);
        }
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        $writer = new Xlsx($spreadsheet);
        $writer->save("php://output");
        exit;
    }

    // Export PDF
    if ($format === 'pdf') {
        require_once '../vendor/autoload.php';
        $pdf = new TCPDF();
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('Lab Access System');
        $pdf->SetTitle(str_replace('_', ' ', ucfirst($exportReportType ?: 'system')) . ' Report');
        $pdf->SetHeaderData('', 0, 'Lab Access Management System', str_replace('_', ' ', ucfirst($exportReportType ?: 'system')) . ' Report');
        $pdf->setHeaderFont(Array('helvetica', '', 10));
        $pdf->setFooterFont(Array('helvetica', '', 8));
        $pdf->SetMargins(15, 27, 15);
        $pdf->SetAutoPageBreak(TRUE, 25);
        $pdf->AddPage();

        $html = '<h2>' . str_replace('_', ' ', ucfirst($exportReportType ?: 'System')) . ' Report</h2>';
        $html .= '<table border="1" cellpadding="4"><thead><tr>';
        foreach ($headers as $col) {
            $html .= '<th>' . htmlspecialchars($col) . '</th>';
        }
        $html .= '</tr></thead><tbody>';

        foreach ($data as $row) {
            $html .= '<tr>';
            foreach ($headers as $col) {
                $key = strtolower(str_replace(' ', '_', $col));
                $html .= '<td>' . htmlspecialchars($row[$key] ?? '') . '</td>';
            }
            $html .= '</tr>';
        }

        $html .= '</tbody></table>';

        $pdf->writeHTML($html, true, false, true, false, '');
        $pdf->Output($filename, 'D');
        exit;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Reports</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-primary bg-gradient text-light">
<div class="container py-5">

    <!-- Search/Report Form -->
    <form method="GET" action="" class="mb-4">
        <div class="input-group">
            <select name="report_type" class="form-select" required>
                <option value="">Select Report</option>
                <option value="rejected_users" <?= (isset($_GET['report_type']) && $_GET['report_type'] === 'rejected_users') ? 'selected' : '' ?>>Rejected Users</option>
                <option value="high_risk_computers" <?= (isset($_GET['report_type']) && $_GET['report_type'] === 'high_risk_computers') ? 'selected' : '' ?>>High Risk Computers</option>
            </select>
            <button type="submit" class="btn btn-primary">Search</button>
        </div>
    </form>

    <!-- Search Results -->
    <?php if ($reportType === 'rejected_users'): ?>
        <div class="card shadow-lg mb-4 bg-white text-dark border-0">
            <div class="card-body">
                <h4 class="card-title text-primary mb-3">Rejected Users</h4>
                <?php if (count($searchResults) > 0): ?>
                    <table class="table table-bordered table-striped align-middle mb-0">
                        <thead class="bg-danger text-white">
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($searchResults as $user): ?>
                                <tr>
                                    <td><?= htmlspecialchars($user['id']) ?></td>
                                    <td><?= htmlspecialchars($user['name']) ?></td>
                                    <td><?= htmlspecialchars($user['email']) ?></td>
                                    <td><span class="badge bg-danger"><?= htmlspecialchars($user['status']) ?></span></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p class="text-muted">No rejected users found.</p>
                <?php endif; ?>
            </div>
        </div>
    <?php elseif ($reportType === 'high_risk_computers'): ?>
        <div class="card shadow-lg mb-4 bg-white text-dark border-0">
            <div class="card-body">
                <h4 class="card-title text-primary mb-3">High Risk Computers</h4>
                <?php if (count($searchResults) > 0): ?>
                    <table class="table table-bordered table-striped align-middle mb-0">
                        <thead class="bg-warning text-dark">
                            <tr>
                                <th>ID</th>
                                <th>Label</th>
                                <th>Status</th>
                                <th>Risk</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($searchResults as $computer): ?>
                                <tr>
                                    <td><?= htmlspecialchars($computer['id']) ?></td>
                                    <td><?= htmlspecialchars($computer['label']) ?></td>
                                    <td><?= htmlspecialchars($computer['status']) ?></td>
                                    <td><span class="badge bg-danger">High Risk</span></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p class="text-muted">No high risk computers found.</p>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- Existing Dashboard Cards -->

    <div class="card shadow-lg mb-4 bg-white text-dark border-0">
        <div class="card-body">
            <h2 class="card-title text-center text-primary mb-4">System Report</h2>
            <div class="row text-center mb-4">
                <div class="col-md-3 mb-3">
                    <div class="stat-box rounded shadow-sm py-3">
                        <div class="h4 mb-1"><?= htmlspecialchars($totalUsers) ?></div>
                        <div>Total Users</div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="stat-box rounded shadow-sm py-3">
                        <div class="h4 mb-1"><?= htmlspecialchars($totalComputers) ?></div>
                        <div>Total Computers</div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="stat-box rounded shadow-sm py-3">
                        <div class="h4 mb-1"><?= htmlspecialchars($availableComputers) ?></div>
                        <div>Available Computers</div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="stat-box rounded shadow-sm py-3">
                        <div class="h4 mb-1"><?= htmlspecialchars($atRisk) ?></div>
                        <div>High Risk Computers</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-lg mb-4 bg-white text-dark border-0">
        <div class="card-body">
            <h4 class="card-title text-primary mb-3">Recent Bookings</h4>
            <div class="table-responsive">
                <table class="table table-bordered table-striped table-primary align-middle mb-0">
                    <thead class="bg-primary text-white">
                        <tr>
                            <th>#</th>
                            <th>User</th>
                            <th>Computer</th>
                            <th>Status</th>
                            <th>Created</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($recentBookings)): ?>
                            <?php foreach ($recentBookings as $i => $booking): ?>
                                <tr>
                                    <td><?= $i + 1 ?></td>
                                    <td><?= htmlspecialchars($booking['user_name']) ?></td>
                                    <td><?= htmlspecialchars($booking['computer_label']) ?></td>
                                    <td>
                                        <span class="badge 
                                            <?= $booking['status'] === 'approved' ? 'bg-success' : ($booking['status'] === 'pending' ? 'bg-warning text-dark' : 'bg-danger') ?>">
                                            <?= ucfirst(htmlspecialchars($booking['status'])) ?>
                                        </span>
                                    </td>
                                    <td><?= htmlspecialchars($booking['created_at']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center text-muted">No recent bookings found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Export Buttons -->
    <?php if (!empty($reportType)): ?>
    <div class="text-center">
        <h5 class="mb-3 text-white">Export Options:</h5>
        <?php
        $baseUrl = '?format=csv&report_type=' . urlencode($reportType);
        ?>
        <a href="<?= $baseUrl ?>" class="btn btn-outline-light me-2"><i class="fas fa-file-csv"></i> Export CSV</a>

        <?php
        $baseUrl = '?format=xlsx&report_type=' . urlencode($reportType);
        ?>
        <a href="<?= $baseUrl ?>" class="btn btn-outline-light me-2"><i class="fas fa-file-excel"></i> Export Excel</a>

        <?php
        $baseUrl = '?format=pdf&report_type=' . urlencode($reportType);
        ?>
        <a href="<?= $baseUrl ?>" class="btn btn-outline-light"><i class="fas fa-file-pdf"></i> Export PDF</a>
    </div>
    <?php else: ?>
    <div class="text-center">
        <h5 class="mb-3 text-white">Export Options:</h5>
        <a href="?format=csv" class="btn btn-outline-light me-2"><i class="fas fa-file-csv"></i> Export CSV</a>
        <a href="?format=xlsx" class="btn btn-outline-light me-2"><i class="fas fa-file-excel"></i> Export Excel</a>
        <a href="?format=pdf" class="btn btn-outline-light"><i class="fas fa-file-pdf"></i> Export PDF</a>
    </div>
    <?php endif; ?>

</div>
</body>
</html>
