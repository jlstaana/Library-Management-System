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
        if (isset($_POST['borrow'])) {
            $book_id = $_POST['book_id'];
            $student_id = $_POST['student_id'];
            $date_borrowed = date('Y-m-d');
            $lib_id = 1; // Default librarian ID
            
            // Check if book is available
            $check_stmt = $pdo->prepare("
                SELECT COUNT(*) FROM borrowed_books 
                WHERE Book_ID = ? AND Date_Returned IS NULL
            ");
            $check_stmt->execute([$book_id]);
            $is_borrowed = $check_stmt->fetchColumn();
            
            if ($is_borrowed > 0) {
                $error = "This book is currently borrowed by another student.";
            } else {
                $stmt = $pdo->prepare("
                    INSERT INTO borrowed_books (Book_ID, Student_ID, Date_Borrowed, Lib_ID) 
                    VALUES (?, ?, ?, ?)
                ");
                $stmt->execute([$book_id, $student_id, $date_borrowed, $lib_id]);
                $success = "Book borrowed successfully!";
            }
            
        } elseif (isset($_POST['return'])) {
            $borrow_id = $_POST['borrow_id'];
            $date_returned = date('Y-m-d');
            
            $stmt = $pdo->prepare("
                UPDATE borrowed_books SET Date_Returned = ? 
                WHERE ID = ? AND Date_Returned IS NULL
            ");
            $stmt->execute([$date_returned, $borrow_id]);
            
            if ($stmt->rowCount() > 0) {
                $success = "Book returned successfully!";
            } else {
                $error = "Book already returned or invalid borrow record.";
            }
        }
    } catch (PDOException $e) {
        $error = "Database error: " . $e->getMessage();
    }
}

// Fetch data for dropdowns
$available_books = $pdo->query("
    SELECT b.*, a.Name as AuthorName, c.Description as CategoryName
    FROM books b 
    LEFT JOIN authors a ON b.Author_ID = a.Author_ID 
    LEFT JOIN book_categories c ON b.Category_ID = c.Category_ID
    WHERE b.Book_ID NOT IN (
        SELECT Book_ID FROM borrowed_books WHERE Date_Returned IS NULL
    )
    ORDER BY b.Title
")->fetchAll(PDO::FETCH_ASSOC);

$students = $pdo->query("SELECT * FROM students ORDER BY Name")->fetchAll(PDO::FETCH_ASSOC);

// Fetch current borrow records
$borrowed_books = $pdo->query("
    SELECT bb.*, b.Title, b.ISBN, a.Name as AuthorName, s.Name as StudentName, 
           s.Email as StudentEmail, s.Student_ID, l.Name as LibrarianName
    FROM borrowed_books bb
    JOIN books b ON bb.Book_ID = b.Book_ID
    JOIN authors a ON b.Author_ID = a.Author_ID
    JOIN students s ON bb.Student_ID = s.Student_ID
    JOIN librarians l ON bb.Lib_ID = l.Lib_ID
    WHERE bb.Date_Returned IS NULL
    ORDER BY bb.Date_Borrowed DESC
")->fetchAll(PDO::FETCH_ASSOC);

// Fetch recent return history
$return_history = $pdo->query("
    SELECT bb.*, b.Title, a.Name as AuthorName, s.Name as StudentName,
           l.Name as LibrarianName, DATEDIFF(bb.Date_Returned, bb.Date_Borrowed) as DurationDays
    FROM borrowed_books bb
    JOIN books b ON bb.Book_ID = b.Book_ID
    JOIN authors a ON b.Author_ID = a.Author_ID
    JOIN students s ON bb.Student_ID = s.Student_ID
    JOIN librarians l ON bb.Lib_ID = l.Lib_ID
    WHERE bb.Date_Returned IS NOT NULL
    ORDER BY bb.Date_Returned DESC
    LIMIT 10
")->fetchAll(PDO::FETCH_ASSOC);

// Statistics
$total_books = $pdo->query("SELECT COUNT(*) FROM books")->fetchColumn();
$total_students = $pdo->query("SELECT COUNT(*) FROM students")->fetchColumn();
$active_borrows = count($borrowed_books);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Borrow/Return - Library System</title>
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
                <h1 class="h3"><i class="fas fa-exchange-alt me-2"></i>Borrow & Return</h1>
                <p class="text-muted">Manage book circulation</p>
            </div>
            <div class="text-end">
                <span class="badge bg-warning"><?php echo $active_borrows; ?> active</span>
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
            <div class="col-md-3 mb-3">
                <div class="card text-center">
                    <div class="card-body">
                        <i class="fas fa-book fa-2x text-primary mb-2"></i>
                        <h3 class="text-primary"><?php echo $total_books; ?></h3>
                        <p class="text-muted mb-0">Total Books</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card text-center">
                    <div class="card-body">
                        <i class="fas fa-check-circle fa-2x text-success mb-2"></i>
                        <h3 class="text-success"><?php echo count($available_books); ?></h3>
                        <p class="text-muted mb-0">Available</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card text-center">
                    <div class="card-body">
                        <i class="fas fa-users fa-2x text-info mb-2"></i>
                        <h3 class="text-info"><?php echo $total_students; ?></h3>
                        <p class="text-muted mb-0">Students</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card text-center">
                    <div class="card-body">
                        <i class="fas fa-clock fa-2x text-warning mb-2"></i>
                        <h3 class="text-warning"><?php echo $active_borrows; ?></h3>
                        <p class="text-muted mb-0">Borrowed</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Borrow Book Section -->
            <div class="col-lg-6 mb-4">
                <div class="card h-100">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-hand-holding me-2"></i>Borrow a Book</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label">Select Student</label>
                                <select name="student_id" class="form-select" required>
                                    <option value="">Choose student...</option>
                                    <?php foreach ($students as $student): ?>
                                        <option value="<?php echo $student['Student_ID']; ?>">
                                            <?php echo htmlspecialchars($student['Name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Select Book</label>
                                <select name="book_id" class="form-select" required>
                                    <option value="">Choose book...</option>
                                    <?php foreach ($available_books as $book): ?>
                                        <option value="<?php echo $book['Book_ID']; ?>">
                                            <?php echo htmlspecialchars($book['Title']); ?> - <?php echo htmlspecialchars($book['AuthorName']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <small class="text-muted">
                                    <?php echo count($available_books); ?> books available
                                </small>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Borrow Date</label>
                                <input type="text" class="form-control" value="<?php echo date('M j, Y'); ?>" readonly>
                            </div>
                            
                            <button type="submit" name="borrow" class="btn btn-primary w-100">Borrow Book</button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Available Books -->
            <div class="col-lg-6 mb-4">
                <div class="card h-100">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-book-open me-2"></i>Available Books</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($available_books)): ?>
                            <div class="text-center py-4">
                                <i class="fas fa-book fa-3x text-muted mb-3"></i>
                                <p class="text-muted">All books are currently borrowed.</p>
                            </div>
                        <?php else: ?>
                            <div style="max-height: 400px; overflow-y: auto;">
                                <?php foreach (array_slice($available_books, 0, 8) as $book): ?>
                                    <div class="border-bottom pb-2 mb-2">
                                        <div class="fw-medium"><?php echo htmlspecialchars($book['Title']); ?></div>
                                        <small class="text-muted">
                                            by <?php echo htmlspecialchars($book['AuthorName']); ?> • 
                                            <?php echo htmlspecialchars($book['CategoryName']); ?>
                                        </small>
                                    </div>
                                <?php endforeach; ?>
                                <?php if (count($available_books) > 8): ?>
                                    <div class="text-center mt-2">
                                        <small class="text-muted">+<?php echo count($available_books) - 8; ?> more available</small>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Currently Borrowed Books -->
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-list me-2"></i>Currently Borrowed</h5>
                <span class="badge bg-<?php echo $active_borrows > 0 ? 'warning' : 'success'; ?>">
                    <?php echo $active_borrows; ?> books
                </span>
            </div>
            <div class="card-body">
                <?php if (empty($borrowed_books)): ?>
                    <div class="text-center py-4">
                        <i class="fas fa-check-circle fa-3x text-muted mb-3"></i>
                        <p class="text-muted">No books are currently borrowed.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Book</th>
                                    <th>Student</th>
                                    <th>Borrowed</th>
                                    <th>Status</th>
                                    <th width="100">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($borrowed_books as $borrow): 
                                    $days_borrowed = (strtotime(date('Y-m-d')) - strtotime($borrow['Date_Borrowed'])) / (60 * 60 * 24);
                                    $days_borrowed = floor($days_borrowed);
                                    $is_overdue = $days_borrowed > 14;
                                    $due_date = date('M j, Y', strtotime($borrow['Date_Borrowed'] . ' +14 days'));
                                ?>
                                <tr class="<?php echo $is_overdue ? 'table-warning' : ''; ?>">
                                    <td>
                                        <div class="fw-medium"><?php echo htmlspecialchars($borrow['Title']); ?></div>
                                        <small class="text-muted">by <?php echo htmlspecialchars($borrow['AuthorName']); ?></small>
                                    </td>
                                    <td>
                                        <?php echo htmlspecialchars($borrow['StudentName']); ?>
                                        <br><small class="text-muted"><?php echo htmlspecialchars($borrow['StudentEmail']); ?></small>
                                    </td>
                                    <td>
                                        <?php echo date('M j, Y', strtotime($borrow['Date_Borrowed'])); ?>
                                        <br><small class="text-muted">Due: <?php echo $due_date; ?></small>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?php echo $is_overdue ? 'danger' : 'success'; ?>">
                                            <?php echo $days_borrowed; ?> days
                                        </span>
                                        <?php if ($is_overdue): ?>
                                            <br><small class="text-danger">Overdue</small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <form method="POST">
                                            <input type="hidden" name="borrow_id" value="<?php echo $borrow['ID']; ?>">
                                            <button type="submit" name="return" class="btn btn-success btn-sm w-100">
                                                Return
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Recent Returns -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-history me-2"></i>Recent Returns</h5>
            </div>
            <div class="card-body">
                <?php if (empty($return_history)): ?>
                    <div class="text-center py-4">
                        <i class="fas fa-clock fa-3x text-muted mb-3"></i>
                        <p class="text-muted">No return history available.</p>
                    </div>
                <?php else: ?>
                    <div class="row">
                        <?php foreach (array_slice($return_history, 0, 6) as $return): ?>
                        <div class="col-md-6 mb-3">
                            <div class="border-start border-3 ps-3">
                                <div class="fw-medium"><?php echo htmlspecialchars($return['Title']); ?></div>
                                <small class="text-muted">
                                    <?php echo htmlspecialchars($return['StudentName']); ?> • 
                                    <?php echo date('M j', strtotime($return['Date_Borrowed'])); ?> - 
                                    <?php echo date('M j', strtotime($return['Date_Returned'])); ?>
                                    <span class="badge bg-light text-dark ms-1"><?php echo $return['DurationDays']; ?> days</span>
                                </small>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
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

        // Modal stability fix
        document.addEventListener('DOMContentLoaded', function() {
            var modals = document.querySelectorAll('.modal');
            modals.forEach(function(modal) {
                modal.addEventListener('show.bs.modal', function () {
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