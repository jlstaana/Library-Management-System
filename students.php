<?php
session_start();
require_once 'config/database.php';

$error = '';
$success = '';

// Handle form submissions
if ($_POST) {
    try {
        if (isset($_POST['add'])) {
            $name = $_POST['name'];
            $email = $_POST['email'];
            $username = $_POST['username'];
            $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
            
            $stmt = $pdo->prepare("INSERT INTO students (Name, Email, Username, Password) VALUES (?, ?, ?, ?)");
            $stmt->execute([$name, $email, $username, $password]);
            $success = "Student added successfully!";
            
        } elseif (isset($_POST['update'])) {
            $id = $_POST['id'];
            $name = $_POST['name'];
            $email = $_POST['email'];
            $username = $_POST['username'];
            
            $stmt = $pdo->prepare("UPDATE students SET Name=?, Email=?, Username=? WHERE Student_ID=?");
            $stmt->execute([$name, $email, $username, $id]);
            $success = "Student updated successfully!";
            
        } elseif (isset($_POST['delete'])) {
            $id = $_POST['id'];
            $stmt = $pdo->prepare("DELETE FROM students WHERE Student_ID=?");
            $stmt->execute([$id]);
            $success = "Student deleted successfully!";
        }
    } catch (PDOException $e) {
        // Simple error handling
        if ($e->getCode() == 23000) {
            $error = "This email or username already exists!";
        } else {
            $error = "Database error: " . $e->getMessage();
        }
    }
}

// Fetch all students
$students = $pdo->query("SELECT * FROM students")->fetchAll(PDO::FETCH_ASSOC);

// Get student statistics
$total_students = count($students);
$active_borrowers = $pdo->query("SELECT COUNT(DISTINCT Student_ID) FROM borrowed_books WHERE Date_Returned IS NULL")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Students - Library System</title>
    <link rel="icon" type="image/jpg" href="\Lab_Exam_LibSys\logo.jpg">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
    <style>
        .professional-success { color: #1e7e34; }
        .professional-teal { color: #20c997; }
        .professional-dark-teal { color: #198754; }
        .professional-olive { color: #6c757d; }
        .btn-success { background-color: #198754; border-color: #198754; }
        .btn-success:hover { background-color: #157347; border-color: #146c43; }
        .text-success { color: #198754 !important; }
        .bg-success { background-color: #198754 !important; }
        .border-success { border-color: #198754 !important; }
    </style>
</head>
<body>
    <?php include 'navbar.php'; ?>

    <div class="container mt-4">
        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3"><i class="fas fa-users me-2"></i>Students</h1>
                <p class="text-muted">Manage student accounts and information</p>
            </div>
            <div class="text-end">
                <span class="badge bg-primary"><i class="fas fa-user-graduate me-1"></i><?php echo $total_students; ?> students</span>
            </div>
        </div>

        <!-- Alert Messages -->
        <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i><?php echo $error; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i><?php echo $success; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Quick Stats -->
        <div class="row mb-4">
            <div class="col-md-4 mb-3">
                <div class="card text-center">
                    <div class="card-body">
                        <i class="fas fa-users fa-2x text-primary mb-2"></i>
                        <h3 class="text-primary"><?php echo $total_students; ?></h3>
                        <p class="text-muted mb-0">Total Students</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="card text-center">
                    <div class="card-body">
                        <i class="fas fa-book-reader fa-2x text-success mb-2"></i>
                        <h3 class="text-success"><?php echo $active_borrowers; ?></h3>
                        <p class="text-muted mb-0">Active Borrowers</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="card text-center">
                    <div class="card-body">
                        <i class="fas fa-chart-line fa-2x text-info mb-2"></i>
                        <h3 class="text-info"><?php echo $total_students > 0 ? round($active_borrowers / $total_students * 100, 1) : 0; ?>%</h3>
                        <p class="text-muted mb-0">Active Rate</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Add Student Form -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-user-plus me-2"></i>Add New Student</h5>
            </div>
            <div class="card-body">
                <form method="POST" class="row g-3">
                    <div class="col-md-3">
                        <div class="input-group">
                            <input type="text" name="name" class="form-control" placeholder="Full Name" required>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="input-group">
                            <input type="email" name="email" class="form-control" placeholder="Email Address" required>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="input-group">
                            <input type="text" name="username" class="form-control" placeholder="Username" required>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="input-group">
                            <input type="password" name="password" class="form-control" placeholder="Password" required>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" name="add" class="btn btn-primary w-100">
                            <i class="fas fa-save me-1"></i>Add
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Students Table -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-list me-2"></i>Students List</h5>
                <span class="badge bg-secondary"><?php echo $total_students; ?> records</span>
            </div>
            <div class="card-body">
                <?php if (empty($students)): ?>
                    <div class="text-center py-5">
                        <i class="fas fa-user-slash fa-3x text-muted mb-3"></i>
                        <h5>No Students Found</h5>
                        <p class="text-muted">Start by adding your first student using the form above.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th><i class="fas fa-id-card me-1"></i>ID</th>
                                    <th><i class="fas fa-user me-1"></i>Name</th>
                                    <th><i class="fas fa-envelope me-1"></i>Email</th>
                                    <th><i class="fas fa-user-tag me-1"></i>Username</th>
                                    <th><i class="fas fa-cog me-1"></i>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($students as $student): ?>
                                <tr>
                                    <td>
                                        <span class="badge bg-light text-dark">#<?php echo $student['Student_ID']; ?></span>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-user-circle text-primary me-2"></i>
                                            <?php echo htmlspecialchars($student['Name']); ?>
                                        </div>
                                    </td>
                                    <td>
                                        <?php echo htmlspecialchars($student['Email']); ?>
                                    </td>
                                    <td>
                                        <?php echo htmlspecialchars($student['Username']); ?>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm" role="group">
                                            <button class="btn btn-outline-primary" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#editModal<?php echo $student['Student_ID']; ?>">
                                                <i class="fas fa-edit me-1"></i>Edit
                                            </button>
                                            <form method="POST" class="d-inline">
                                                <input type="hidden" name="id" value="<?php echo $student['Student_ID']; ?>">
                                                <button type="submit" name="delete" 
                                                        class="btn btn-outline-danger" 
                                                        onclick="return confirm('Are you sure you want to delete this student?')">
                                                    <i class="fas fa-trash me-1"></i>Delete
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Edit Modals - Placed outside the table to prevent flickering -->
        <?php foreach ($students as $student): ?>
        <div class="modal fade" id="editModal<?php echo $student['Student_ID']; ?>" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form method="POST">
                        <div class="modal-header">
                            <h5 class="modal-title">
                                <i class="fas fa-edit me-2"></i>Edit Student
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <input type="hidden" name="id" value="<?php echo $student['Student_ID']; ?>">
                            <div class="mb-3">
                                <label class="form-label"><i class="fas fa-user me-2"></i>Full Name</label>
                                <input type="text" name="name" class="form-control" 
                                       value="<?php echo htmlspecialchars($student['Name']); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label"><i class="fas fa-envelope me-2"></i>Email Address</label>
                                <input type="email" name="email" class="form-control" 
                                       value="<?php echo htmlspecialchars($student['Email']); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label"><i class="fas fa-id-card me-2"></i>Username</label>
                                <input type="text" name="username" class="form-control" 
                                       value="<?php echo htmlspecialchars($student['Username']); ?>" required>
                            </div>
                            <div class="alert alert-light">
                                <small class="text-muted">
                                    <i class="fas fa-info-circle me-1"></i>
                                    Password cannot be changed through this form for security reasons.
                                </small>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                <i class="fas fa-times me-1"></i>Cancel
                            </button>
                            <button type="submit" name="update" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i>Save Changes
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <?php endforeach; ?>

        <!-- Student Activity Summary -->
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Student Activity Summary</h5>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-md-3">
                        <div class="border rounded p-3">
                            <i class="fas fa-users text-primary fa-2x mb-2"></i>
                            <h4><?php echo $total_students; ?></h4>
                            <small class="text-muted">Registered Students</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="border rounded p-3">
                            <i class="fas fa-book-reader text-success fa-2x mb-2"></i>
                            <h4><?php echo $active_borrowers; ?></h4>
                            <small class="text-muted">Active Borrowers</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="border rounded p-3">
                            <i class="fas fa-user-clock text-warning fa-2x mb-2"></i>
                            <h4><?php echo $total_students - $active_borrowers; ?></h4>
                            <small class="text-muted">Inactive Students</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="border rounded p-3">
                            <i class="fas fa-percentage text-info fa-2x mb-2"></i>
                            <h4><?php echo $total_students > 0 ? round($active_borrowers / $total_students * 100, 1) : 0; ?>%</h4>
                            <small class="text-muted">Participation Rate</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Auto-dismiss alerts after 5 seconds
        setTimeout(() => {
            document.querySelectorAll('.alert').forEach(alert => {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 5000);

        // Additional modal stability fix
        document.addEventListener('DOMContentLoaded', function() {
            var modals = document.querySelectorAll('.modal');
            modals.forEach(function(modal) {
                modal.addEventListener('show.bs.modal', function () {
                    // Ensure proper backdrop
                    var backdrops = document.querySelectorAll('.modal-backdrop');
                    backdrops.forEach(function(backdrop) {
                        backdrop.remove();
                    });
                });
            });
        });
    </script>
</body>
</html>