<?php
session_start();
require_once 'config/database.php';

$error = '';
$success = '';

// Fetch authors and categories for dropdowns
$authors = $pdo->query("SELECT * FROM authors ORDER BY Name")->fetchAll(PDO::FETCH_ASSOC);
$categories = $pdo->query("SELECT * FROM book_categories ORDER BY Description")->fetchAll(PDO::FETCH_ASSOC);

// Handle form submissions
if ($_POST) {
    try {
        if (isset($_POST['add'])) {
            $title = $_POST['title'];
            $author_id = $_POST['author_id'];
            $publisher = $_POST['publisher'];
            $edition = $_POST['edition'];
            $isbn = $_POST['isbn'];
            $category_id = $_POST['category_id'];
            
            $stmt = $pdo->prepare("INSERT INTO books (Title, Author_ID, Publisher, Edition, ISBN, Category_ID) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$title, $author_id, $publisher, $edition, $isbn, $category_id]);
            $success = "Book added successfully!";
            
        } elseif (isset($_POST['update'])) {
            $id = $_POST['id'];
            $title = $_POST['title'];
            $author_id = $_POST['author_id'];
            $publisher = $_POST['publisher'];
            $edition = $_POST['edition'];
            $isbn = $_POST['isbn'];
            $category_id = $_POST['category_id'];
            
            $stmt = $pdo->prepare("UPDATE books SET Title=?, Author_ID=?, Publisher=?, Edition=?, ISBN=?, Category_ID=? WHERE Book_ID=?");
            $stmt->execute([$title, $author_id, $publisher, $edition, $isbn, $category_id, $id]);
            $success = "Book updated successfully!";
            
        } elseif (isset($_POST['delete'])) {
            $id = $_POST['id'];
            $stmt = $pdo->prepare("DELETE FROM books WHERE Book_ID=?");
            $stmt->execute([$id]);
            $success = "Book deleted successfully!";
        }
    } catch (PDOException $e) {
        if ($e->getCode() == 23000) {
            $error = "This ISBN already exists in the system!";
        } else {
            $error = "Database error: " . $e->getMessage();
        }
    }
}

// Fetch all books with author and category names
$books = $pdo->query("
    SELECT b.*, a.Name as AuthorName, c.Description as CategoryName 
    FROM books b 
    LEFT JOIN authors a ON b.Author_ID = a.Author_ID 
    LEFT JOIN book_categories c ON b.Category_ID = c.Category_ID
    ORDER BY b.Title
")->fetchAll(PDO::FETCH_ASSOC);

// Get book statistics
$total_books = count($books);
$available_books = $pdo->query("
    SELECT COUNT(*) FROM books b 
    WHERE b.Book_ID NOT IN (
        SELECT Book_ID FROM borrowed_books WHERE Date_Returned IS NULL
    )
")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Books - Library System</title>
    <link rel="icon" type="image/jpg" href="logo.jpg">
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
                <h1 class="h3"><i class="fas fa-book me-2"></i>Books</h1>
                <p class="text-muted">Manage library book collection</p>
            </div>
            <div class="text-end">
                <span class="badge bg-primary"><i class="fas fa-book me-1"></i><?php echo $total_books; ?> books</span>
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
                        <i class="fas fa-book fa-2x text-primary mb-2"></i>
                        <h3 class="text-primary"><?php echo $total_books; ?></h3>
                        <p class="text-muted mb-0">Total Books</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="card text-center">
                    <div class="card-body">
                        <i class="fas fa-check-circle fa-2x text-success mb-2"></i>
                        <h3 class="text-success"><?php echo $available_books; ?></h3>
                        <p class="text-muted mb-0">Available Now</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="card text-center">
                    <div class="card-body">
                        <i class="fas fa-user-edit fa-2x text-info mb-2"></i>
                        <h3 class="text-info"><?php echo count($authors); ?></h3>
                        <p class="text-muted mb-0">Authors</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Add Book Form -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-plus-circle me-2"></i>Add New Book</h5>
            </div>
            <div class="card-body">
                <form method="POST" class="row g-3">
                    <div class="col-md-3">
                        <input type="text" name="title" class="form-control" placeholder="Book Title" required>
                    </div>
                    <div class="col-md-2">
                        <select name="author_id" class="form-select" required>
                            <option value="">Select Author</option>
                            <?php foreach ($authors as $author): ?>
                                <option value="<?php echo $author['Author_ID']; ?>"><?php echo htmlspecialchars($author['Name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <input type="text" name="publisher" class="form-control" placeholder="Publisher">
                    </div>
                    <div class="col-md-1">
                        <input type="text" name="edition" class="form-control" placeholder="Edition">
                    </div>
                    <div class="col-md-2">
                        <input type="text" name="isbn" class="form-control" placeholder="ISBN">
                    </div>
                    <div class="col-md-2">
                        <select name="category_id" class="form-select" required>
                            <option value="">Select Category</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?php echo $category['Category_ID']; ?>"><?php echo htmlspecialchars($category['Description']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-12 mt-2">
                        <button type="submit" name="add" class="btn btn-primary">
                            Add Book
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Books Table -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-list me-2"></i>Books Collection</h5>
                <span class="badge bg-secondary"><?php echo $total_books; ?> books</span>
            </div>
            <div class="card-body">
                <?php if (empty($books)): ?>
                    <div class="text-center py-5">
                        <i class="fas fa-book-open fa-3x text-muted mb-3"></i>
                        <h5>No Books Found</h5>
                        <p class="text-muted">Start by adding your first book to the library.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Title</th>
                                    <th>Author</th>
                                    <th>Publisher</th>
                                    <th>Edition</th>
                                    <th>ISBN</th>
                                    <th>Category</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($books as $book): ?>
                                <tr>
                                    <td><span class="badge bg-light text-dark">#<?php echo $book['Book_ID']; ?></span></td>
                                    <td>
                                        <div class="fw-medium"><?php echo htmlspecialchars($book['Title']); ?></div>
                                    </td>
                                    <td>
                                        <?php echo htmlspecialchars($book['AuthorName']); ?>
                                    </td>
                                    <td>
                                        <?php if ($book['Publisher']): ?>
                                            <?php echo htmlspecialchars($book['Publisher']); ?>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($book['Edition']): ?>
                                            <span class="badge bg-light text-dark"><?php echo htmlspecialchars($book['Edition']); ?></span>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($book['ISBN']): ?>
                                            <code><?php echo htmlspecialchars($book['ISBN']); ?></code>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border">
                                            <?php echo htmlspecialchars($book['CategoryName']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm" role="group">
                                            <button class="btn btn-outline-primary" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#editModal<?php echo $book['Book_ID']; ?>">
                                                <i class="fas fa-edit me-1"></i>Edit
                                            </button>
                                            <form method="POST" class="d-inline">
                                                <input type="hidden" name="id" value="<?php echo $book['Book_ID']; ?>">
                                                <button type="submit" name="delete" 
                                                        class="btn btn-outline-danger" 
                                                        onclick="return confirm('Are you sure you want to delete this book?')">
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
        <?php foreach ($books as $book): ?>
        <div class="modal fade" id="editModal<?php echo $book['Book_ID']; ?>" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <form method="POST">
                        <div class="modal-header">
                            <h5 class="modal-title">
                                <i class="fas fa-edit me-2"></i>Edit Book
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <input type="hidden" name="id" value="<?php echo $book['Book_ID']; ?>">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Title</label>
                                    <input type="text" name="title" class="form-control" 
                                           value="<?php echo htmlspecialchars($book['Title']); ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Author</label>
                                    <select name="author_id" class="form-select" required>
                                        <?php foreach ($authors as $author): ?>
                                            <option value="<?php echo $author['Author_ID']; ?>" 
                                                <?php echo $author['Author_ID'] == $book['Author_ID'] ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($author['Name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Publisher</label>
                                    <input type="text" name="publisher" class="form-control" 
                                           value="<?php echo htmlspecialchars($book['Publisher']); ?>">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Edition</label>
                                    <input type="text" name="edition" class="form-control" 
                                           value="<?php echo htmlspecialchars($book['Edition']); ?>">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">ISBN</label>
                                    <input type="text" name="isbn" class="form-control" 
                                           value="<?php echo htmlspecialchars($book['ISBN']); ?>">
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Category</label>
                                <select name="category_id" class="form-select" required>
                                    <?php foreach ($categories as $category): ?>
                                        <option value="<?php echo $category['Category_ID']; ?>" 
                                            <?php echo $category['Category_ID'] == $book['Category_ID'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($category['Description']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                Cancel
                            </button>
                            <button type="submit" name="update" class="btn btn-primary">
                                Save Changes
                            </button>
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