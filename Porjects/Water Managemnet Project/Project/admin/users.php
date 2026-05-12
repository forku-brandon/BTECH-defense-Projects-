<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';

// Check Admin Access
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    die("Access Denied: Admin role required.");
}

$action = $_GET['action'] ?? '';
$error = '';
$success = '';

// Handle Actions (Add, Edit, Delete)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_user'])) {
        $username = sanitizeInput($_POST['username']);
        $password = $_POST['password'];
        $role = sanitizeInput($_POST['role']);
        $companyName = sanitizeInput($_POST['company_name']);

        if (strlen($password) < 8) {
            $error = "Password must be at least 8 characters.";
        } else {
            try {
                $hash = password_hash($password, PASSWORD_BCRYPT);
                $stmt = $pdo->prepare("INSERT INTO users (username, password_hash, role, company_name) VALUES (?, ?, ?, ?)");
                $stmt->execute([$username, $hash, $role, $companyName]);
                $success = "User added successfully.";
            } catch (PDOException $e) {
                if ($e->getCode() == 23000) {
                    $error = "Username already exists.";
                } else {
                    $error = "Database error: " . $e->getMessage();
                }
            }
        }
    } elseif (isset($_POST['delete_user'])) {
        $id = intval($_POST['user_id']);
        if ($id !== $_SESSION['user_id']) {
            $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
            $stmt->execute([$id]);
            $success = "User deleted successfully.";
        } else {
            $error = "You cannot delete your own account.";
        }
    }
}

// Fetch Users
$stmt = $pdo->query("SELECT id, username, role, company_name, created_at FROM users ORDER BY created_at DESC");
$users = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <style>
        .dashboard-container { display: flex; min-height: 100vh; }
        .sidebar { width: 260px; background: white; border-right: 1px solid var(--border-color); padding: 1.5rem; display: flex; flex-direction: column; position: fixed; height: 100vh; }
        .main-content { flex: 1; margin-left: 260px; padding: 2rem; background: var(--bg-body); }
        .sidebar-menu { margin-top: 2rem; flex: 1; }
        .menu-item { display: flex; align-items: center; gap: 0.75rem; padding: 0.75rem 1rem; color: var(--text-muted); border-radius: var(--radius-sm); margin-bottom: 0.5rem; font-weight: 500; }
        .menu-item:hover, .menu-item.active { background: var(--primary-light); color: var(--primary-blue); }
        table { width: 100%; border-collapse: collapse; background: white; border-radius: var(--radius-md); overflow: hidden; box-shadow: var(--shadow-sm); }
        th, td { padding: 1rem 1.5rem; text-align: left; border-bottom: 1px solid var(--border-color); }
        th { background: var(--primary-light); color: var(--text-muted); font-size: 0.85rem; text-transform: uppercase; }
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem; }
    </style>
</head>
<body>

<div class="dashboard-container">
    <aside class="sidebar">
        <a href="../dashboard/index.php" class="logo" style="margin-bottom: 0.5rem;">
            <i class="ph-fill ph-drop logo-icon"></i> WataReport
        </a>
        <div style="font-size: 0.8rem; color: var(--text-muted); margin-bottom: 2rem; padding-left: 0.5rem;">Admin Portal</div>
        
        <div class="sidebar-menu">
            <a href="../dashboard/index.php" class="menu-item"><i class="ph ph-squares-four"></i> Dashboard</a>
            <a href="users.php" class="menu-item active"><i class="ph ph-users"></i> Manage Users</a>
        </div>
        
        <div style="border-top: 1px solid var(--border-color); padding-top: 1.5rem;">
            <a href="../dashboard/logout.php" class="menu-item" style="color: #DC2626;">
                <i class="ph ph-sign-out"></i> Logout
            </a>
        </div>
    </aside>

    <main class="main-content">
        <h2>Manage Staff Accounts</h2>
        
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <div class="card" style="margin-bottom: 2rem;">
            <div class="card-header"><h4>Add New User</h4></div>
            <div class="card-body">
                <form action="users.php" method="POST">
                    <input type="hidden" name="add_user" value="1">
                    <div class="form-row">
                        <div>
                            <label class="form-label">Username</label>
                            <input type="text" name="username" class="form-control" required>
                        </div>
                        <div>
                            <label class="form-label">Password</label>
                            <input type="password" name="password" class="form-control" required minlength="8" placeholder="Min 8 characters">
                        </div>
                        <div>
                            <label class="form-label">Role</label>
                            <select name="role" class="form-control" required>
                                <option value="water_company">Water Company Staff</option>
                                <option value="admin">System Admin</option>
                            </select>
                        </div>
                        <div>
                            <label class="form-label">Company Name</label>
                            <input type="text" name="company_name" class="form-control" required placeholder="e.g. Camwater">
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary"><i class="ph-bold ph-plus"></i> Add User</button>
                </form>
            </div>
        </div>

        <div style="background: white; border-radius: var(--radius-md); box-shadow: var(--shadow-sm); overflow: hidden;">
            <table>
                <thead>
                    <tr>
                        <th>Username</th>
                        <th>Role</th>
                        <th>Company</th>
                        <th>Created At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $u): ?>
                    <tr>
                        <td style="font-weight: 500;"><?php echo htmlspecialchars($u['username']); ?></td>
                        <td>
                            <span class="badge <?php echo $u['role'] === 'admin' ? 'badge-warning' : 'badge-secondary'; ?>">
                                <?php echo htmlspecialchars($u['role']); ?>
                            </span>
                        </td>
                        <td><?php echo htmlspecialchars($u['company_name']); ?></td>
                        <td><?php echo date('M d, Y', strtotime($u['created_at'])); ?></td>
                        <td>
                            <?php if ($u['id'] !== $_SESSION['user_id']): ?>
                            <form action="users.php" method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this user?');">
                                <input type="hidden" name="delete_user" value="1">
                                <input type="hidden" name="user_id" value="<?php echo $u['id']; ?>">
                                <button type="submit" class="btn btn-outline btn-sm" style="color: #DC2626; border-color: #DC2626;"><i class="ph-bold ph-trash"></i> Delete</button>
                            </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </main>
</div>

</body>
</html>
