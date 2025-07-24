<?php
include __DIR__ . '/../includes/db.php';
session_start();

// CSRF Token Generation
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Function to validate CSRF token
function validateCSRF($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// Function to sanitize input
function sanitizeInput($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

// Function to validate email
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

// Success/Error message handling
$message = '';
$messageType = '';

try {
    // Handle Add User
    if (isset($_POST['add_user'])) {
        if (!validateCSRF($_POST['csrf_token'] ?? '')) {
            throw new Exception('Invalid CSRF token');
        }

        $name = sanitizeInput($_POST['name'] ?? '');
        $email = sanitizeInput($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        // Validation
        if (empty($name) || empty($email) || empty($password)) {
            throw new Exception('All fields are required');
        }

        if (!isValidEmail($email)) {
            throw new Exception('Invalid email format');
        }

        if (strlen($password) < 8) {
            throw new Exception('Password must be at least 8 characters long');
        }

        // Check if email already exists
        $checkStmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $checkStmt->bind_param("s", $email);
        $checkStmt->execute();
        if ($checkStmt->get_result()->num_rows > 0) {
            throw new Exception('Email already exists');
        }
        $checkStmt->close();

        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $name, $email, $hashedPassword);

        if ($stmt->execute()) {
            $message = 'User added successfully';
            $messageType = 'success';
        } else {
            throw new Exception('Failed to add user');
        }
        $stmt->close();
        
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }

    // Handle Update User
    if (isset($_POST['update_user'])) {
        if (!validateCSRF($_POST['csrf_token'] ?? '')) {
            throw new Exception('Invalid CSRF token');
        }

        $id = intval($_POST['edit_id'] ?? 0);
        $name = sanitizeInput($_POST['edit_name'] ?? '');
        $email = sanitizeInput($_POST['edit_email'] ?? '');
        $password = $_POST['edit_password'] ?? '';

        // Validation
        if ($id <= 0 || empty($name) || empty($email)) {
            throw new Exception('Invalid input data');
        }

        if (!isValidEmail($email)) {
            throw new Exception('Invalid email format');
        }

        // Check if email already exists for other users
        $checkStmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $checkStmt->bind_param("si", $email, $id);
        $checkStmt->execute();
        if ($checkStmt->get_result()->num_rows > 0) {
            throw new Exception('Email already exists');
        }
        $checkStmt->close();

        if (!empty($password)) {
            if (strlen($password) < 8) {
                throw new Exception('Password must be at least 8 characters long');
            }
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE users SET name=?, email=?, password=? WHERE id=?");
            $stmt->bind_param("sssi", $name, $email, $hashedPassword, $id);
        } else {
            $stmt = $conn->prepare("UPDATE users SET name=?, email=? WHERE id=?");
            $stmt->bind_param("ssi", $name, $email, $id);
        }

        if ($stmt->execute() && $stmt->affected_rows > 0) {
            $message = 'User updated successfully';
            $messageType = 'success';
        } else {
            throw new Exception('Failed to update user or no changes made');
        }
        $stmt->close();
        
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }

    // Handle delete
    if (isset($_GET['delete']) && isset($_GET['csrf_token'])) {
        if (!validateCSRF($_GET['csrf_token'])) {
            throw new Exception('Invalid CSRF token');
        }

        $deleteId = intval($_GET['delete']);
        if ($deleteId <= 0) {
            throw new Exception('Invalid user ID');
        }

        $stmt = $conn->prepare("DELETE FROM users WHERE id=?");
        $stmt->bind_param("i", $deleteId);

        if ($stmt->execute() && $stmt->affected_rows > 0) {
            $message = 'User deleted successfully';
            $messageType = 'success';
        } else {
            throw new Exception('Failed to delete user or user not found');
        }
        $stmt->close();
        
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }

} catch (Exception $e) {
    $message = $e->getMessage();
    $messageType = 'error';
}

// Pagination
$page = max(1, intval($_GET['page'] ?? 1));
$limit = 10;
$offset = ($page - 1) * $limit;

// Search functionality
$search = sanitizeInput($_GET['search'] ?? '');
$searchCondition = '';
$searchParams = [];
$searchTypes = '';

if (!empty($search)) {
    $searchCondition = ' WHERE name LIKE ? OR email LIKE ?';
    $searchTerm = '%' . $search . '%';
    $searchParams = [$searchTerm, $searchTerm];
    $searchTypes = 'ss';
}

// Get total count for pagination
$countQuery = "SELECT COUNT(*) as total FROM users" . $searchCondition;
if (!empty($searchParams)) {
    $countStmt = $conn->prepare($countQuery);
    $countStmt->bind_param($searchTypes, ...$searchParams);
    $countStmt->execute();
    $totalUsers = $countStmt->get_result()->fetch_assoc()['total'];
    $countStmt->close();
} else {
    $totalUsers = $conn->query($countQuery)->fetch_assoc()['total'];
}

$totalPages = ceil($totalUsers / $limit);

// Fetch users with pagination
$query = "SELECT id, name, email, created_at FROM users" . $searchCondition . " ORDER BY created_at DESC LIMIT ? OFFSET ?";
$stmt = $conn->prepare($query);

if (!empty($searchParams)) {
    $stmt->bind_param($searchTypes . 'ii', ...array_merge($searchParams, [$limit, $offset]));
} else {
    $stmt->bind_param('ii', $limit, $offset);
}

$stmt->execute();
$users = $stmt->get_result();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management - Hookcraft Avenue</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../asset/dashboard.css">
    <style>
        .main-content {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
        
        .user-card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
            backdrop-filter: blur(10px);
            background: rgba(255,255,255,0.95);
        }
        
        .card-header-custom {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            border-radius: 15px 15px 0 0;
            border: none;
            color: white;
            padding: 1.5rem;
        }
        
        .search-container {
            background: rgba(255,255,255,0.1);
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            backdrop-filter: blur(10px);
        }
        
        .search-input {
            border: none;
            border-radius: 25px;
            padding: 0.75rem 1.5rem;
            background: rgba(255,255,255,0.9);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .search-input:focus {
            background: white;
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
            border: 2px solid #11998e;
        }
        
        .table-modern {
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
            background: white;
        }
        
        .table-modern thead {
            background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
        }
        
        .table-modern tbody tr {
            transition: all 0.3s ease;
            border: none;
        }
        
        .table-modern tbody tr:hover {
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.1) 0%, rgba(118, 75, 162, 0.1) 100%);
            transform: scale(1.02);
        }
        
        .user-avatar {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 1.2rem;
        }
        
        .btn-modern {
            border-radius: 8px;
            padding: 0.5rem 1rem;
            font-weight: 500;
            transition: all 0.3s ease;
            border: none;
        }
        
        .btn-modern:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        
        .btn-add {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            color: white;
        }
        
        .btn-edit {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
        }
        
        .btn-delete {
            background: linear-gradient(135deg, #fc466b 0%, #3f5efb 100%);
            color: white;
        }
        
        .btn-search {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 25px;
        }
        
        .status-badge {
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.85rem;
        }
        
        .status-active {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            color: white;
        }
        
        .pagination-modern .page-link {
            border: none;
            margin: 0 2px;
            border-radius: 8px;
            color: #667eea;
            transition: all 0.3s ease;
        }
        
        .pagination-modern .page-item.active .page-link {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .pagination-modern .page-link:hover {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            transform: translateY(-2px);
        }
        
        .modal-content {
            border: none;
            border-radius: 15px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.15);
        }
        
        .modal-header {
            border-radius: 15px 15px 0 0;
            border-bottom: 1px solid #eee;
        }
        
        .form-control {
            border-radius: 8px;
            border: 2px solid #e9ecef;
            transition: all 0.3s ease;
            padding: 0.75rem 1rem;
        }
        
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        
        .password-strength {
            font-size: 0.85rem;
            margin-top: 0.5rem;
        }
        
        .strength-weak { color: #dc3545; }
        .strength-medium { color: #ffc107; }
        .strength-strong { color: #28a745; }
        
        .stats-container {
            display: flex;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }
        
        .stat-card {
            background: rgba(255,255,255,0.1);
            border-radius: 12px;
            padding: 1rem;
            text-align: center;
            color: white;
            flex: 1;
            backdrop-filter: blur(10px);
        }
        
        .stat-number {
            font-size: 2rem;
            font-weight: bold;
            display: block;
        }
        
        .stat-label {
            font-size: 0.9rem;
            opacity: 0.8;
        }
        
        .alert-modern {
            border: none;
            border-radius: 12px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>

<?php include __DIR__ . '/sidebar.php'; ?>
<?php include __DIR__ . '/header.php'; ?>

<div class="main-content p-4">
    <?php if (!empty($message)): ?>
        <div class="alert alert-modern alert-<?= $messageType === 'success' ? 'success' : 'danger' ?> alert-dismissible fade show" role="alert">
            <i class="fas fa-<?= $messageType === 'success' ? 'check-circle' : 'exclamation-triangle' ?> me-2"></i>
            <?= htmlspecialchars($message) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    
    <div class="stats-container">
        <div class="stat-card">
            <span class="stat-number"><?= $totalUsers ?></span>
            <span class="stat-label">Total Users</span>
        </div>
        <div class="stat-card">
            <span class="stat-number"><?= $totalPages ?></span>
            <span class="stat-label">Pages</span>
        </div>
        <div class="stat-card">
            <span class="stat-number"><?= $page ?></span>
            <span class="stat-label">Current Page</span>
        </div>
    </div>

    <div class="user-card card">
        <div class="card-header-custom d-flex justify-content-between align-items-center">
            <div>
                <h4 class="mb-1"><i class="fas fa-users me-2"></i>User Management</h4>
                <small class="opacity-75">Manage system users and permissions</small>
            </div>
            <button class="btn btn-add btn-modern" data-bs-toggle="modal" data-bs-target="#addUserModal">
                <i class="fas fa-user-plus me-2"></i>Add User
            </button>
        </div>
        
        <div class="card-body">
            <!-- Search Form -->
            <div class="search-container">
                <form method="GET" class="d-flex gap-2">
                    <input type="text" name="search" class="form-control search-input flex-grow-1" 
                           placeholder="Search by name or email..." 
                           value="<?= htmlspecialchars($search) ?>">
                    <button class="btn btn-search btn-modern" type="submit">
                        <i class="fas fa-search"></i>
                    </button>
                    <?php if (!empty($search)): ?>
                        <a href="user.php" class="btn btn-outline-light btn-modern">
                            <i class="fas fa-times"></i>
                        </a>
                    <?php endif; ?>
                </form>
            </div>

            <div class="table-responsive">
                <table class="table table-modern table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th class="py-3">Avatar</th>
                            <th class="py-3">Name</th>
                            <th class="py-3">Email</th>
                            <th class="py-3">Created</th>
                            <th class="py-3">Status</th>
                            <th class="py-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($users && $users->num_rows > 0): ?>
                            <?php while ($row = $users->fetch_assoc()): ?>
                                <tr>
                                    <td>
                                        <div class="user-avatar">
                                            <?= strtoupper(substr($row['name'], 0, 1)) ?>
                                        </div>
                                    </td>
                                    <td class="text-start">
                                        <div class="fw-bold text-primary"><?= htmlspecialchars($row["name"]) ?></div>
                                        <small class="text-muted">ID: <?= $row["id"] ?></small>
                                    </td>
                                    <td class="text-start"><?= htmlspecialchars($row["email"]) ?></td>
                                    <td>
                                        <div class="fw-bold"><?= date("M j, Y", strtotime($row["created_at"])) ?></div>
                                        <small class="text-muted"><?= date("g:i A", strtotime($row["created_at"])) ?></small>
                                    </td>
                                    <td>
                                        <span class="status-badge status-active">
                                            <i class="fas fa-check-circle me-1"></i>Active
                                        </span>
                                    </td>
                                    <td>
                                        <div class="d-flex gap-2">
                                            <button class="btn btn-edit btn-modern btn-sm editBtn"
                                                data-id="<?= $row['id'] ?>"
                                                data-name="<?= htmlspecialchars($row['name'], ENT_QUOTES) ?>"
                                                data-email="<?= htmlspecialchars($row['email'], ENT_QUOTES) ?>"
                                                data-bs-toggle="modal" data-bs-target="#editModal"
                                                title="Edit User">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn btn-delete btn-modern btn-sm deleteBtn"
                                                data-id="<?= $row['id'] ?>"
                                                data-name="<?= htmlspecialchars($row['name'], ENT_QUOTES) ?>"
                                                title="Delete User">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="py-5 text-muted text-center">
                                    <i class="fas fa-users fa-3x mb-3 d-block"></i>
                                    <?= !empty($search) ? 'No users found matching your search.' : 'No users found. Add your first user!' ?>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
                <nav aria-label="User pagination" class="mt-4">
                    <ul class="pagination pagination-modern justify-content-center">
                        <?php if ($page > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?= $page - 1 ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?>">
                                    <i class="fas fa-chevron-left"></i>
                                </a>
                            </li>
                        <?php endif; ?>

                        <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                            <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                                <a class="page-link" href="?page=<?= $i ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?>"><?= $i ?></a>
                            </li>
                        <?php endfor; ?>

                        <?php if ($page < $totalPages): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?= $page + 1 ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?>">
                                    <i class="fas fa-chevron-right"></i>
                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </nav>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Add User Modal -->
<div class="modal fade" id="addUserModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="post" id="addUserForm">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">Add New User</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <input type="text" name="name" class="form-control" required maxlength="100" placeholder="Full Name">
                    </div>
                    <div class="mb-3">
                        <input type="email" name="email" class="form-control" required maxlength="150" placeholder="Email Address">
                    </div>
                    <div class="mb-3">
                        <div class="input-group">
                            <input type="password" name="password" class="form-control" id="add_password" required minlength="8" placeholder="Password">
                            <button type="button" class="btn btn-outline-secondary" onclick="togglePassword('add_password', this)">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        <div id="add_password_strength" class="password-strength"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button name="add_user" type="submit" class="btn btn-primary">Add User</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit User Modal -->
<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="post" id="editUserForm">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                <input type="hidden" name="edit_id" id="edit_id">
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title">Edit User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Full Name</label>
                        <input type="text" name="edit_name" id="edit_name" class="form-control" required maxlength="100">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Email Address</label>
                        <input type="email" name="edit_email" id="edit_email" class="form-control" required maxlength="150">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">New Password</label>
                        <div class="input-group">
                            <input type="password" name="edit_password" id="edit_password" class="form-control" 
                                   placeholder="Leave blank to keep current password" minlength="8">
                            <button type="button" class="btn btn-outline-secondary" onclick="togglePassword('edit_password', this)">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        <div id="edit_password_strength" class="password-strength"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button name="update_user" type="submit" class="btn btn-primary">Update User</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">Confirm Delete</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="text-center">
                    <i class="fas fa-exclamation-triangle fa-3x text-warning mb-3"></i>
                    <p>Are you sure you want to delete user <strong id="deleteUserName"></strong>?</p>
                    <p class="text-danger small">This action cannot be undone.</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <a href="#" id="confirmDeleteBtn" class="btn btn-danger">Delete User</a>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    const csrfToken = '<?= $_SESSION['csrf_token'] ?>';

    // Fill edit modal with user info
    document.querySelectorAll('.editBtn').forEach(btn => {
        btn.addEventListener('click', function () {
            document.getElementById('edit_id').value = this.dataset.id;
            document.getElementById('edit_name').value = this.dataset.name;
            document.getElementById('edit_email').value = this.dataset.email;
            document.getElementById('edit_password').value = '';
            document.getElementById('edit_password_strength').innerHTML = '';
        });
    });

    // Handle delete button clicks
    document.querySelectorAll('.deleteBtn').forEach(btn => {
        btn.addEventListener('click', function () {
            const userId = this.dataset.id;
            const userName = this.dataset.name;

            document.getElementById('deleteUserName').textContent = userName;
            document.getElementById('confirmDeleteBtn').href = `user.php?delete=${userId}&csrf_token=${encodeURIComponent(csrfToken)}`;

            new bootstrap.Modal(document.getElementById('deleteModal')).show();
        });
    });

    // Toggle password visibility
    function togglePassword(id, btn) {
        const input = document.getElementById(id);
        const icon = btn.querySelector('i');
        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.replace('fa-eye', 'fa-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.replace('fa-eye-slash', 'fa-eye');
        }
    }

    // Password strength checker
    function checkPasswordStrength(password) {
        let strength = 0;
        let feedback = [];

        if (password.length >= 8) strength++;
        else feedback.push('At least 8 characters');

        if (/[a-z]/.test(password)) strength++;
        else feedback.push('Lowercase letter');

        if (/[A-Z]/.test(password)) strength++;
        else feedback.push('Uppercase letter');

        if (/[0-9]/.test(password)) strength++;
        else feedback.push('Number');

        if (/[^A-Za-z0-9]/.test(password)) strength++;
        else feedback.push('Special character');

        return { strength, feedback };
    }

    // Add password strength indicators
    ['add_password', 'edit_password'].forEach(id => {
        const input = document.getElementById(id);
        const strengthDiv = document.getElementById(id + '_strength');

        input.addEventListener('input', function() {
            if (this.value.length === 0) {
                strengthDiv.innerHTML = '';
                return;
            }

            const { strength, feedback } = checkPasswordStrength(this.value);
            let strengthText = '';
            let strengthClass = '';

            if (strength < 3) {
                strengthText = 'Weak';
                strengthClass = 'strength-weak';
            } else if (strength < 5) {
                strengthText = 'Medium';
                strengthClass = 'strength-medium';
            } else {
                strengthText = 'Strong';
                strengthClass = 'strength-strong';
            }

            strengthDiv.innerHTML = `
                <div class="${strengthClass}">
                    <i class="fas fa-shield-alt me-1"></i>Strength: ${strengthText}
                </div>
                ${feedback.length > 0 ? '<div class="small text-muted mt-1">Missing: ' + feedback.join(', ') + '</div>' : ''}
            `;
        });
    });

    // Form validation
    document.getElementById('addUserForm').addEventListener('submit', function(e) {
        const password = document.getElementById('add_password').value;
        const name = this.querySelector('input[name="name"]').value.trim();
        const email = this.querySelector('input[name="email"]').value.trim();
        
        if (!name || !email || !password) {
            e.preventDefault();
            alert('All fields are required');
            return;
        }
        
        if (password.length < 8) {
            e.preventDefault();
            alert('Password must be at least 8 characters long');
            return;
        }
    });

    document.getElementById('editUserForm').addEventListener('submit', function(e) {
        const password = document.getElementById('edit_password').value;
        const name = document.getElementById('edit_name').value.trim();
        const email = document.getElementById('edit_email').value.trim();
        
        if (!name || !email) {
            e.preventDefault();
            alert('Name and email are required');
            return;
        }
        
        if (password && password.length < 8) {
            e.preventDefault();
            alert('Password must be at least 8 characters long');
            return;
        }
    });

    // Auto-hide alerts after 5 seconds
    document.addEventListener('DOMContentLoaded', function() {
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(function(alert) {
            setTimeout(function() {
                alert.style.transition = 'opacity 0.5s';
                alert.style.opacity = '0';
                setTimeout(function() {
                    if (alert.parentNode) {
                        alert.remove();
                    }
                }, 500);
            }, 5000);
        });
    });

    // Enhanced form interactions
    document.querySelectorAll('input').forEach(input => {
        input.addEventListener('focus', function() {
            this.parentElement.style.transform = 'scale(1.02)';
        });
        
        input.addEventListener('blur', function() {
            this.parentElement.style.transform = 'scale(1)';
        });
    });

    // Search form enhancement
    document.querySelector('input[name="search"]').addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            this.closest('form').submit();
        }
    });

    // Modal enhancements
    document.querySelectorAll('.modal').forEach(modal => {
        modal.addEventListener('shown.bs.modal', function() {
            const firstInput = this.querySelector('input:not([type="hidden"])');
            if (firstInput) {
                firstInput.focus();
            }
        });
        
        modal.addEventListener('hidden.bs.modal', function() {
            // Clear form data when modal is closed
            const form = this.querySelector('form');
            if (form) {
                form.reset();
                // Clear password strength indicators
                const strengthDivs = form.querySelectorAll('.password-strength');
                strengthDivs.forEach(div => div.innerHTML = '');
            }
        });
    });
</script>

</body>
</html>