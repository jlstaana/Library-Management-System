<?php
session_start();
require_once 'config/database.php';

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

$error = '';
$success = '';

// Handle form submissions
if ($_POST) {
    try {
        if (isset($_POST['add'])) {
            $name = $_POST['name'];
            $email = $_POST['email'];
            $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
            $type_id = $_POST['type_id'];
            
            $stmt = $pdo->prepare("INSERT INTO librarians (Name, Email, Password, Type_ID) VALUES (?, ?, ?, ?)");
            $stmt->execute([$name, $email, $password, $type_id]);
            $success = "Librarian added successfully!";
            
        } elseif (isset($_POST['update'])) {
            $id = $_POST['id'];
            $name = $_POST['name'];
            $email = $_POST['email'];
            $type_id = $_POST['type_id'];
            
            $stmt = $pdo->prepare("UPDATE librarians SET Name=?, Email=?, Type_ID=? WHERE Lib_ID=?");
            $stmt->execute([$name, $email, $type_id, $id]);
            $success = "Librarian updated successfully!";
            
        } elseif (isset($_POST['delete'])) {
            $id = $_POST['id'];
            
            // Prevent deletion of the last librarian
            $count_stmt = $pdo->query("SELECT COUNT(*) FROM librarians");
            $librarian_count = $count_stmt->fetchColumn();
            
            if ($librarian_count <= 1) {
                $error = "Cannot delete the last librarian. There must be at least one librarian in the system.";
            } else {
                $stmt = $pdo->prepare("DELETE FROM librarians WHERE Lib_ID=?");
                $stmt->execute([$id]);
                $success = "Librarian deleted successfully!";
            }
        }
    } catch (PDOException $e) {
        if ($e->getCode() == 23000) {
            $error = "This email already exists for another librarian!";
        } else {
            $error = "Database error: " . $e->getMessage();
        }
    }
}

// Fetch librarian types
$librarian_types = $pdo->query("SELECT * FROM librarian_types")->fetchAll(PDO::FETCH_ASSOC);

// Fetch all librarians with their types
$librarians = $pdo->query("
    SELECT l.*, lt.Description as TypeDescription 
    FROM librarians l 
    JOIN librarian_types lt ON l.Type_ID = lt.Type_ID 
    ORDER BY l.Name
")->fetchAll(PDO::FETCH_ASSOC);

// Get statistics
$total_books = $pdo->query("SELECT COUNT(*) FROM books")->fetchColumn();
$total_students = $pdo->query("SELECT COUNT(*) FROM students")->fetchColumn();
$active_borrows = $pdo->query("SELECT COUNT(*) FROM borrowed_books WHERE Date_Returned IS NULL")->fetchColumn();
$total_authors = $pdo->query("SELECT COUNT(*) FROM authors")->fetchColumn();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Library Management System</title>
    <link rel="icon" type="image/jpg" href="\Lab_Exam_LibSys\logo.jpg">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
    <style>
        .professional-success { color: #176128ff; }
        .professional-teal { color: #176128ff; }
        .professional-dark-teal { color: 176128ff; }
        .professional-olive { color: #6c757d; }
        .btn-success { background-color: #176128ff; border-color: #176128ff; }
        .btn-success:hover { background-color: #176128ff; border-color: #176128ff; }
        .text-success { color: #176128ff !important; }
        .bg-success { background-color: #176128ff !important; }
        .border-success { border-color: #176128ff !important; }
        
        .dashboard-card {
            transition: transform 0.2s ease-in-out;
            border: none;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .dashboard-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.15);
        }
        .quick-action {
            border: 1px solid #e9ecef;
            border-radius: 8px;
            padding: 1.5rem;
            text-align: center;
            transition: all 0.3s ease;
        }
        .quick-action:hover {
            background-color: #f8f9fa;
            border-color: #007bff;
        }
        .stat-number {
            font-size: 2.5rem;
            font-weight: 700;
        }
    </style>
</head>
<body>
    <?php include 'navbar.php'; ?>

    <div class="container mt-4">
        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3"><i class="fas fa-library me-2"></i>Library Dashboard</h1>
                <p class="text-muted">Welcome to your library management system</p>
            </div>
            <div class="text-end">
                <span class="badge bg-primary"><?php echo date('M j, Y'); ?></span>
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

        <!-- Main Statistics -->
        <div class="row mb-4">
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card dashboard-card border-start border-primary border-4">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="text-muted">Total Books</h6>
                                <h2 class="stat-number text-primary"><?php echo $total_books; ?></h2>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-book fa-2x text-primary opacity-50"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card dashboard-card border-start border-success border-4">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="text-muted">Total Students</h6>
                                <h2 class="stat-number text-success"><?php echo $total_students; ?></h2>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-users fa-2x text-success opacity-50"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card dashboard-card border-start border-info border-4">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="text-muted">Active Borrows</h6>
                                <h2 class="stat-number text-info"><?php echo $active_borrows; ?></h2>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-exchange-alt fa-2x text-info opacity-50"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card dashboard-card border-start border-warning border-4">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="text-muted">Authors</h6>
                                <h2 class="stat-number text-warning"><?php echo $total_authors; ?></h2>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-user-edit fa-2x text-warning opacity-50"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-bolt me-2"></i>Quick Actions</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <a href="books.php" class="text-decoration-none">
                                    <div class="quick-action">
                                        <i class="fas fa-book fa-3x text-primary mb-3"></i>
                                        <h5>Manage Books</h5>
                                        <p class="text-muted small">Add, edit, or remove books</p>
                                    </div>
                                </a>
                            </div>
                            <div class="col-md-3 mb-3">
                                <a href="students.php" class="text-decoration-none">
                                    <div class="quick-action">
                                        <i class="fas fa-users fa-3x text-success mb-3"></i>
                                        <h5>Manage Students</h5>
                                        <p class="text-muted small">Student accounts & records</p>
                                    </div>
                                </a>
                            </div>
                            <div class="col-md-3 mb-3">
                                <a href="borrow_return.php" class="text-decoration-none">
                                    <div class="quick-action">
                                        <i class="fas fa-exchange-alt fa-3x text-info mb-3"></i>
                                        <h5>Borrow/Return</h5>
                                        <p class="text-muted small">Book circulation</p>
                                    </div>
                                </a>
                            </div>
                            <div class="col-md-3 mb-3">
                                <a href="authors.php" class="text-decoration-none">
                                    <div class="quick-action">
                                        <i class="fas fa-user-edit fa-3x text-warning mb-3"></i>
                                        <h5>Manage Authors</h5>
                                        <p class="text-muted small">Author information</p>
                                    </div>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Activity & System Overview -->
        <div class="row">
            <!-- Recent Books -->
            <div class="col-lg-6 mb-4">
                <div class="card h-100">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="fas fa-clock me-2"></i>Recent Books</h5>
                        <a href="books.php" class="btn btn-sm btn-outline-primary">View All</a>
                    </div>
                    <div class="card-body">
                        <?php
                        $recent_books = $pdo->query("
                            SELECT b.*, a.Name as AuthorName 
                            FROM books b 
                            LEFT JOIN authors a ON b.Author_ID = a.Author_ID 
                            ORDER BY b.Book_ID DESC 
                            LIMIT 5
                        ")->fetchAll(PDO::FETCH_ASSOC);
                        ?>
                        
                        <?php if (empty($recent_books)): ?>
                            <div class="text-center py-4">
                                <i class="fas fa-book-open fa-3x text-muted mb-3"></i>
                                <p class="text-muted">No books added yet.</p>
                            </div>
                        <?php else: ?>
                            <div class="list-group list-group-flush">
                                <?php foreach ($recent_books as $book): ?>
                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <div class="fw-medium"><?php echo htmlspecialchars($book['Title']); ?></div>
                                        <small class="text-muted">by <?php echo htmlspecialchars($book['AuthorName']); ?></small>
                                    </div>
                                    <span class="badge bg-light text-dark">#<?php echo $book['Book_ID']; ?></span>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- System Status -->
            <div class="col-lg-6 mb-4">
                <div class="card h-100">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-chart-bar me-2"></i>System Status</h5>
                    </div>
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-6 mb-3">
                                <div class="border rounded p-3">
                                    <i class="fas fa-check-circle fa-2x text-success mb-2"></i>
                                    <h4><?php echo $total_books - $active_borrows; ?></h4>
                                    <small class="text-muted">Available Books</small>
                                </div>
                            </div>
                            <div class="col-6 mb-3">
                                <div class="border rounded p-3">
                                    <i class="fas fa-user-check fa-2x text-info mb-2"></i>
                                    <h4><?php echo count($librarians); ?></h4>
                                    <small class="text-muted">Active Librarians</small>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="border rounded p-3">
                                    <i class="fas fa-chart-line fa-2x text-primary mb-2"></i>
                                    <h4><?php echo $total_books > 0 ? round(($total_books - $active_borrows) / $total_books * 100, 1) : 0; ?>%</h4>
                                    <small class="text-muted">Availability Rate</small>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="border rounded p-3">
                                    <i class="fas fa-percentage fa-2x text-warning mb-2"></i>
                                    <h4><?php echo $total_students > 0 ? round($active_borrows / $total_students * 100, 1) : 0; ?>%</h4>
                                    <small class="text-muted">Borrow Rate</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Overdue Books Alert -->
        <?php
        $overdue_books = $pdo->query("
            SELECT COUNT(*) as overdue_count 
            FROM borrowed_books 
            WHERE Date_Returned IS NULL 
            AND DATEDIFF(CURDATE(), Date_Borrowed) > 14
        ")->fetch(PDO::FETCH_ASSOC);
        ?>
        
        <?php if ($overdue_books['overdue_count'] > 0): ?>
        <div class="alert alert-warning mt-4">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Attention:</strong> There are <?php echo $overdue_books['overdue_count']; ?> overdue book(s) that need to be returned.
                </div>
                <a href="borrow_return.php" class="btn btn-warning btn-sm">View Details</a>
            </div>
        </div>
        <?php endif; ?>

        <!-- Library Information -->
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Library Information</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6><i class="fas fa-building me-2"></i>Library Hours</h6>
                        <ul class="list-unstyled">
                            <li><small>Monday - Friday: 8:00 AM - 8:00 PM</small></li>
                            <li><small>Saturday: 9:00 AM - 5:00 PM</small></li>
                            <li><small>Sunday: Closed</small></li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <h6><i class="fas fa-bookmark me-2"></i>Quick Links</h6>
                        <div class="d-flex flex-wrap gap-2">
                            <a href="books.php" class="btn btn-outline-primary btn-sm">Books Management</a>
                            <a href="students.php" class="btn btn-outline-success btn-sm">Student Records</a>
                            <a href="borrow_return.php" class="btn btn-outline-info btn-sm">Circulation Desk</a>
                            <a href="authors.php" class="btn btn-outline-warning btn-sm">Authors Catalog</a>
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

        // Simple chart animation
        document.addEventListener('DOMContentLoaded', function() {
            const counters = document.querySelectorAll('.stat-number');
            counters.forEach(counter => {
                const target = parseInt(counter.textContent);
                let current = 0;
                const increment = target / 50;
                
                const updateCounter = () => {
                    if (current < target) {
                        current += increment;
                        counter.textContent = Math.ceil(current);
                        setTimeout(updateCounter, 25);
                    } else {
                        counter.textContent = target;
                    }
                };
                
                updateCounter();
            });
        });
    </script>
</body>
</html>