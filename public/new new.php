<div class="container py-5">

    <!-- SEARCH FORM: Add this here -->
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

    <!-- Existing cards start here -->
    <div class="card shadow-lg mb-4 bg-white text-dark border-0">
        <!-- Your existing System Report card here -->
    </div>
    <!-- ... rest of your code ... -->
