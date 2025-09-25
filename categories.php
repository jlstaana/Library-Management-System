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
            $description = $_POST['description'];
            
            $stmt = $pdo->prepare("INSERT INTO book_categories (Description) VALUES (?)");
            $stmt->execute([$description]);
            $success = "Category added successfully!";
            
        } elseif (isset($_POST['update'])) {
            $id = $_POST['id'];
            $description = $_POST['description'];
            
            $stmt = $pdo->prepare("UPDATE book_categories SET Description=? WHERE Category_ID=?");
            $stmt->execute([$description, $id]);
            $success = "Category updated successfully!";
            
        } elseif (isset($_POST['delete'])) {
            $id = $_POST['id'];
            
            // Check if category has books before deleting
            $check_stmt = $pdo->prepare("SELECT COUNT(*) FROM books WHERE Category_ID = ?");
            $check_stmt->execute([$id]);
            $book_count = $check_stmt->fetchColumn();
            
            if ($book_count > 0) {
                $error = "Cannot delete category. There are {$book_count} book(s) in this category.";
            } else {
                $stmt = $pdo->prepare("DELETE FROM book_categories WHERE Category_ID=?");
                $stmt->execute([$id]);
                $success = "Category deleted successfully!";
            }
        }
    } catch (PDOException $e) {
        $error = "Database error: " . $e->getMessage();
    }
}

// Fetch all categories with book counts
$categories = $pdo->query("
    SELECT c.*, COUNT(b.Book_ID) as book_count 
    FROM book_categories c 
    LEFT JOIN books b ON c.Category_ID = b.Category_ID 
    GROUP BY c.Category_ID 
    ORDER BY c.Description
")->fetchAll(PDO::FETCH_ASSOC);

$total_books = $pdo->query("SELECT COUNT(*) FROM books")->fetchColumn();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Categories - Library System</title>
  <link rel="icon" type="image/jpg" href="\Lab_Exam_LibSys\logo.jpg">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
  <link href="css/style.css" rel="stylesheet">

  <style>
    .btn-success { background-color: #198754; border-color: #198754; }
    .btn-success:hover { background-color: #157347; border-color: #146c43; }
    .text-success { color: #198754 !important; }
    .bg-success { background-color: #198754 !important; }
    .border-success { border-color: #198754 !important; }

    .card-title {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    /* Smooth modal transition */
    .modal.fade .modal-dialog {
        transition: transform 0.2s ease-out;
    }

    /* Non-glitch blurred backdrop */
    .modal-backdrop {
        background: rgba(0, 0, 0, 0.5) !important;
        position: relative;
    }
    .modal-backdrop::before {
        content: "";
        position: absolute;
        inset: 0;
        backdrop-filter: blur(2px);
    }

    /* Keep modal content sharp */
    .modal-content {
        transform: translateZ(0);
    }
  </style>
</head>
<body>
  <?php include 'navbar.php'; ?>

  <div class="container mt-4">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
      <div>
        <h1 class="h3"><i class="fas fa-tags me-2"></i>Categories</h1>
        <p class="text-muted">Manage book categories</p>
      </div>
      <div class="text-end">
        <span class="badge bg-primary">
          <i class="fas fa-tag me-1"></i><?php echo count($categories); ?> categories
        </span>
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
      <div class="col-md-6 mb-3">
        <div class="card text-center">
          <div class="card-body">
            <i class="fas fa-tags fa-2x text-primary mb-2"></i>
            <h3 class="text-primary"><?php echo count($categories); ?></h3>
            <p class="text-muted mb-0">Total Categories</p>
          </div>
        </div>
      </div>
      <div class="col-md-6 mb-3">
        <div class="card text-center">
          <div class="card-body">
            <i class="fas fa-book fa-2x text-success mb-2"></i>
            <h3 class="text-success"><?php echo $total_books; ?></h3>
            <p class="text-muted mb-0">Total Books</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Add Category Form -->
    <div class="card mb-4">
      <div class="card-header">
        <h5 class="mb-0"><i class="fas fa-plus-circle me-2"></i>Add New Category</h5>
      </div>
      <div class="card-body">
        <form method="POST" class="row g-3">
          <div class="col-md-10">
            <div class="input-group">
              <span class="input-group-text"><i class="fas fa-tag"></i></span>
              <input type="text" name="description" class="form-control" placeholder="Category name (e.g., Fiction, Science, Technology)" required>
            </div>
          </div>
          <div class="col-md-2">
            <button type="submit" name="add" class="btn btn-primary w-100">
              <i class="fas fa-save me-1"></i>Add Category
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Categories Grid -->
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="fas fa-list me-2"></i>Categories</h5>
        <span class="badge bg-secondary"><?php echo count($categories); ?> total</span>
      </div>
      <div class="card-body">
        <?php if (empty($categories)): ?>
          <div class="text-center py-5">
            <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
            <h5>No Categories Found</h5>
            <p class="text-muted">Start by adding your first category using the form above.</p>
          </div>
        <?php else: ?>
          <div class="row">
            <?php foreach ($categories as $category): ?>
            <div class="col-md-6 col-lg-4 mb-3">
              <div class="card h-100">
                <div class="card-body">
                  <div class="d-flex justify-content-between align-items-start mb-3">
                    <h6 class="card-title mb-0">
                      <i class="fas fa-tag text-primary me-2"></i><?php echo htmlspecialchars($category['Description']); ?>
                    </h6>
                    <span class="badge bg-light text-dark border">
                      <i class="fas fa-book me-1"></i><?php echo $category['book_count']; ?>
                    </span>
                  </div>
                  <div class="d-grid gap-2">
                    <button class="btn btn-outline-primary btn-sm" 
                            data-bs-toggle="modal" 
                            data-bs-target="#editModal<?php echo $category['Category_ID']; ?>">
                      <i class="fas fa-edit me-1"></i>Edit
                    </button>
                    <form method="POST" class="d-grid">
                      <input type="hidden" name="id" value="<?php echo $category['Category_ID']; ?>">
                      <button type="submit" name="delete" 
                              class="btn btn-outline-danger btn-sm">
                        <i class="fas fa-trash me-1"></i>Delete
                      </button>
                    </form>
                  </div>
                </div>
              </div>
            </div>

            <!-- Edit Modal -->
            <div class="modal fade" id="editModal<?php echo $category['Category_ID']; ?>" tabindex="-1" aria-labelledby="editModalLabel<?php echo $category['Category_ID']; ?>" aria-hidden="true">
              <div class="modal-dialog">
                <div class="modal-content">
                  <div class="modal-header">
                    <h5 class="modal-title" id="editModalLabel<?php echo $category['Category_ID']; ?>">
                      <i class="fas fa-edit me-2"></i>Edit Category
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                  </div>
                  <form method="POST">
                    <div class="modal-body">
                      <input type="hidden" name="id" value="<?php echo $category['Category_ID']; ?>">
                      <div class="mb-3">
                        <label class="form-label"><i class="fas fa-tag me-2"></i>Category Name</label>
                        <input type="text" name="description" class="form-control" 
                               value="<?php echo htmlspecialchars($category['Description']); ?>" required>
                      </div>
                      <div class="alert alert-light">
                        <small class="text-muted">
                          <i class="fas fa-info-circle me-1"></i>
                          Contains <?php echo $category['book_count']; ?> book(s)
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
          </div>
        <?php endif; ?>
      </div>
    </div>

    <!-- Categories Summary -->
    <div class="card mt-4">
      <div class="card-header">
        <h5 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Categories Summary</h5>
      </div>
      <div class="card-body">
        <div class="row text-center">
          <div class="col-md-4">
            <div class="border rounded p-3">
              <i class="fas fa-tags text-primary fa-2x mb-2"></i>
              <h4><?php echo count($categories); ?></h4>
              <small class="text-muted">Total Categories</small>
            </div>
          </div>
          <div class="col-md-4">
            <div class="border rounded p-3">
              <i class="fas fa-book text-success fa-2x mb-2"></i>
              <h4><?php echo $total_books; ?></h4>
              <small class="text-muted">Total Books</small>
            </div>
          </div>
          <div class="col-md-4">
            <div class="border rounded p-3">
              <i class="fas fa-calculator text-info fa-2x mb-2"></i>
              <h4><?php echo count($categories) > 0 ? round($total_books / count($categories), 1) : 0; ?></h4>
              <small class="text-muted">Avg Books per Category</small>
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

    // Enhanced delete confirmation
    document.querySelectorAll('form[method="POST"]').forEach(form => {
      const deleteBtn = form.querySelector('button[name="delete"]');
      if (deleteBtn) {
        deleteBtn.addEventListener('click', function(e) {
          if (!confirm('Are you sure you want to delete this category? This action cannot be undone.')) {
            e.preventDefault();
          }
        });
      }
    });
  </script>
</body>
</html>
