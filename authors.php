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
            
            $stmt = $pdo->prepare("INSERT INTO authors (Name, Email) VALUES (?, ?)");
            $stmt->execute([$name, $email]);
            $success = "Author added successfully!";
            
        } elseif (isset($_POST['update'])) {
            $id = $_POST['id'];
            $name = $_POST['name'];
            $email = $_POST['email'];
            
            $stmt = $pdo->prepare("UPDATE authors SET Name=?, Email=? WHERE Author_ID=?");
            $stmt->execute([$name, $email, $id]);
            $success = "Author updated successfully!";
            
        } elseif (isset($_POST['delete'])) {
            $id = $_POST['id'];
            
            // Check if author has books before deleting
            $check_stmt = $pdo->prepare("SELECT COUNT(*) FROM books WHERE Author_ID = ?");
            $check_stmt->execute([$id]);
            $book_count = $check_stmt->fetchColumn();
            
            if ($book_count > 0) {
                $error = "Cannot delete author. There are {$book_count} book(s) associated with this author.";
            } else {
                $stmt = $pdo->prepare("DELETE FROM authors WHERE Author_ID=?");
                $stmt->execute([$id]);
                $success = "Author deleted successfully!";
            }
        }
    } catch (PDOException $e) {
        if ($e->getCode() == 23000) {
            $error = "This email already exists for another author!";
        } else {
            $error = "Database error: " . $e->getMessage();
        }
    }
}

// Fetch all authors with book counts
$authors = $pdo->query("
    SELECT a.*, COUNT(b.Book_ID) as book_count 
    FROM authors a 
    LEFT JOIN books b ON a.Author_ID = b.Author_ID 
    GROUP BY a.Author_ID 
    ORDER BY a.Name
")->fetchAll(PDO::FETCH_ASSOC);

$total_books = $pdo->query("SELECT COUNT(*) FROM books")->fetchColumn();
$avg_books = count($authors) > 0 ? round($total_books / count($authors), 1) : 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Authors - Library System</title>
    <link rel="icon" type="image/jpg" href="\Lab_Exam_LibSys\logo.jpg">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
    
</head>
<body>
    <?php include 'navbar.php'; ?>

    <div class="container mt-4">
        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3"><i class="fas fa-user-edit me-2"></i>Authors</h1>
                <p class="text-muted">Manage library authors</p>
            </div>
            <div class="text-end">
                <span class="badge bg-primary"><?php echo count($authors); ?> authors</span>
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
                        <i class="fas fa-user-edit fa-2x text-primary mb-2"></i>
                        <h3 class="text-primary"><?php echo count($authors); ?></h3>
                        <p class="text-muted mb-0">Total Authors</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="card text-center">
                    <div class="card-body">
                        <i class="fas fa-book fa-2x text-success mb-2"></i>
                        <h3 class="text-success"><?php echo $total_books; ?></h3>
                        <p class="text-muted mb-0">Total Books</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="card text-center">
                    <div class="card-body">
                        <i class="fas fa-chart-bar fa-2x text-info mb-2"></i>
                        <h3 class="text-info"><?php echo $avg_books; ?></h3>
                        <p class="text-muted mb-0">Avg per Author</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Add Author Form -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-plus-circle me-2"></i>Add New Author</h5>
            </div>
            <div class="card-body">
                <form method="POST" class="row g-3">
                    <div class="col-md-5">
                        <input type="text" name="name" class="form-control" placeholder="Author Name" required>
                    </div>
                    <div class="col-md-5">
                        <input type="email" name="email" class="form-control" placeholder="Email Address" required>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" name="add" class="btn btn-primary w-100">Add Author</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Authors Table -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-list me-2"></i>Authors List</h5>
                <span class="badge bg-secondary"><?php echo count($authors); ?> records</span>
            </div>
            <div class="card-body">
                <?php if (empty($authors)): ?>
                    <div class="text-center py-5">
                        <i class="fas fa-user-edit fa-3x text-muted mb-3"></i>
                        <h5>No Authors Found</h5>
                        <p class="text-muted">Add your first author using the form above.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Books</th>
                                    <th width="150">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($authors as $author): ?>
                                <tr>
                                    <td>
                                        <div class="fw-medium"><?php echo htmlspecialchars($author['Name']); ?></div>
                                        <small class="text-muted">ID: <?php echo $author['Author_ID']; ?></small>
                                    </td>
                                    <td><?php echo htmlspecialchars($author['Email']); ?></td>
                                    <td>
                                        <span class="badge bg-light text-dark border">
                                            <?php echo $author['book_count']; ?> books
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm" role="group">
                                            <button class="btn btn-outline-primary" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#editModal<?php echo $author['Author_ID']; ?>">
                                                <i class="fas fa-edit me-1"></i>Edit
                                            </button>
                                            <form method="POST" class="d-inline">
                                                <input type="hidden" name="id" value="<?php echo $author['Author_ID']; ?>">
                                                <button type="submit" name="delete" 
                                                        class="btn btn-outline-danger" 
                                                        onclick="return confirm('Delete this author?')">
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

        <!-- Edit Modals - Placed outside the table -->
        <?php foreach ($authors as $author): ?>
        <div class="modal fade" id="editModal<?php echo $author['Author_ID']; ?>" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form method="POST">
                        <div class="modal-header">
                            <h5 class="modal-title"><i class="fas fa-edit me-2"></i>Edit Author</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <input type="hidden" name="id" value="<?php echo $author['Author_ID']; ?>">
                            <div class="mb-3">
                                <label class="form-label">Author Name</label>
                                <input type="text" name="name" class="form-control" 
                                       value="<?php echo htmlspecialchars($author['Name']); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Email Address</label>
                                <input type="email" name="email" class="form-control" 
                                       value="<?php echo htmlspecialchars($author['Email']); ?>" required>
                            </div>
                            <div class="alert alert-light">
                                <small class="text-muted">
                                    This author has <?php echo $author['book_count']; ?> book(s) in the library.
                                </small>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" name="update" class="btn btn-primary">Save Changes</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
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