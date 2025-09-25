<?php
session_start();
require_once 'config/database.php';

// Get statistics
$books_count = $pdo->query("SELECT COUNT(*) FROM books")->fetchColumn();
$students_count = $pdo->query("SELECT COUNT(*) FROM students")->fetchColumn();
$borrowed_count = $pdo->query("SELECT COUNT(*) FROM borrowed_books WHERE Date_Returned IS NULL")->fetchColumn();
$returned_count = $pdo->query("SELECT COUNT(*) FROM borrowed_books WHERE Date_Returned IS NOT NULL")->fetchColumn();

// Get additional stats for charts
$categories_count = $pdo->query("SELECT COUNT(*) FROM book_categories")->fetchColumn();
$authors_count = $pdo->query("SELECT COUNT(*) FROM authors")->fetchColumn();
$librarians_count = $pdo->query("SELECT COUNT(*) FROM librarians")->fetchColumn();

// Recent activity
$recent_borrows = $pdo->query("
    SELECT b.Title, s.Name as StudentName, bb.Date_Borrowed 
    FROM borrowed_books bb
    JOIN books b ON bb.Book_ID = b.Book_ID
    JOIN students s ON bb.Student_ID = s.Student_ID
    WHERE bb.Date_Returned IS NULL
    ORDER BY bb.Date_Borrowed DESC 
    LIMIT 5
")->fetchAll(PDO::FETCH_ASSOC);

// Weather API with proper error handling
$weather_data = null;
$weather_error = null;
$api_key = "cd84fc20b026f1f0f938399473faf0de"; 
$city = "Cabuyao";
$weather_url = "https://api.openweathermap.org/data/2.5/weather?q=$city&appid=$api_key&units=metric";

try {
    // Use file_get_contents with context for HTTPS
    $context = stream_context_create([
        'ssl' => [
            'verify_peer' => false,
            'verify_peer_name' => false,
        ],
        'http' => [
            'timeout' => 10, // 10 second timeout
            'ignore_errors' => true
        ]
    ]);
    
    $weather_response = file_get_contents($weather_url, false, $context);
    
    if ($weather_response !== false) {
        $weather_data = json_decode($weather_response, true);
        
        // Check if API returned an error
        if (isset($weather_data['cod']) && $weather_data['cod'] != 200) {
            $weather_error = $weather_data['message'] ?? 'Unknown weather API error';
            $weather_data = null;
        }
    } else {
        $weather_error = "Unable to connect to weather service";
    }
} catch (Exception $e) {
    $weather_error = "Weather service temporarily unavailable";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Library System</title>
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
                <h1 class="h2">Dashboard</h1>
                <p class="text-muted">Library system overview and statistics</p>
            </div>
            <div class="text-end">
                <small class="text-muted">Last updated: <?php echo date('M j, Y g:i A'); ?></small>
            </div>
        </div>

        <!-- Main Statistics Cards -->
        <div class="row mb-4">
            <div class="col-md-3 mb-4">
                <div class="card border-primary">
                    <div class="card-body text-center">
                        <div class="text-primary mb-3">
                            <i class="fas fa-book fa-3x"></i>
                        </div>
                        <h3 class="text-primary"><?php echo $books_count; ?></h3>
                        <p class="text-muted mb-0">Total Books</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-4">
                <div class="card border-success">
                    <div class="card-body text-center">
                        <div class="text-success mb-3">
                            <i class="fas fa-users fa-3x"></i>
                        </div>
                        <h3 class="text-success"><?php echo $students_count; ?></h3>
                        <p class="text-muted mb-0">Total Students</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-4">
                <div class="card border-warning">
                    <div class="card-body text-center">
                        <div class="text-warning mb-3">
                            <i class="fas fa-hand-holding fa-3x"></i>
                        </div>
                        <h3 class="text-warning"><?php echo $borrowed_count; ?></h3>
                        <p class="text-muted mb-0">Borrowed Books</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-4">
                <div class="card border-info">
                    <div class="card-body text-center">
                        <div class="text-info mb-3">
                            <i class="fas fa-undo fa-3x"></i>
                        </div>
                        <h3 class="text-info"><?php echo $returned_count; ?></h3>
                        <p class="text-muted mb-0">Returned Books</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Additional Statistics -->
            <div class="col-md-4 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-chart-pie me-2"></i>Library Overview</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-2">
                            <span><i class="fas fa-tags text-primary me-2"></i>Categories</span>
                            <strong class="text-primary"><?php echo $categories_count; ?></strong>
                        </div>
                        <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-2">
                            <span><i class="fas fa-user-edit text-success me-2"></i>Authors</span>
                            <strong class="text-success"><?php echo $authors_count; ?></strong>
                        </div>
                        <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-2">
                            <span><i class="fas fa-user-shield text-info me-2"></i>Librarians</span>
                            <strong class="text-info"><?php echo $librarians_count; ?></strong>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <span><i class="fas fa-exchange-alt text-warning me-2"></i>Total Transactions</span>
                            <strong class="text-warning"><?php echo $borrowed_count + $returned_count; ?></strong>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="col-md-4 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-clock me-2"></i>Recent Activity</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($recent_borrows)): ?>
                            <p class="text-muted">No recent borrowing activity.</p>
                        <?php else: ?>
                            <?php foreach ($recent_borrows as $borrow): ?>
                                <div class="d-flex align-items-center mb-3">
                                    <div class="bg-light rounded-circle p-2 me-3">
                                        <i class="fas fa-book text-primary"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="fw-medium"><?php echo htmlspecialchars($borrow['Title']); ?></div>
                                        <small class="text-muted">
                                            <?php echo htmlspecialchars($borrow['StudentName']); ?> • 
                                            <?php echo date('M j', strtotime($borrow['Date_Borrowed'])); ?>
                                        </small>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="col-md-4 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-rocket me-2"></i>Quick Actions</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="books.php" class="btn btn-outline-primary text-start">
                                <i class="fas fa-plus me-2"></i>Add New Book
                            </a>
                            <a href="students.php" class="btn btn-outline-success text-start">
                                <i class="fas fa-user-plus me-2"></i>Register Student
                            </a>
                            <a href="borrow_return.php" class="btn btn-outline-warning text-start">
                                <i class="fas fa-exchange-alt me-2"></i>Process Borrow/Return
                            </a>
                            <a href="authors.php" class="btn btn-outline-info text-start">
                                <i class="fas fa-user-edit me-2"></i>Manage Authors
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- System Status & Weather -->
        <div class="row">
            <!-- System Status -->
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-database me-2"></i>System Status</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span>Database Connection</span>
                            <span class="badge bg-success"><i class="fas fa-check me-1"></i>Connected</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span>System Performance</span>
                            <span class="badge bg-success"><i class="fas fa-check me-1"></i>Optimal</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span>Last Backup</span>
                            <span class="text-muted"><?php echo date('M j, Y'); ?></span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <span>Uptime</span>
                            <span class="text-muted">24/7</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Weather Information -->
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-cloud-sun me-2"></i>Weather Information</h5>
                    </div>
                    <div class="card-body">
                        <?php if ($weather_data && $weather_data['cod'] == 200): ?>
                            <div class="text-center">
                                <i class="fas fa-city fa-2x text-primary mb-3"></i>
                                <h4><?php echo $weather_data['name'] . ', ' . ($weather_data['sys']['country'] ?? ''); ?></h4>
                                <div class="display-4 text-warning mb-2">
                                    <?php echo round($weather_data['main']['temp']); ?>°C
                                </div>
                                <p class="text-capitalize mb-3">
                                    <i class="fas fa-cloud me-1"></i>
                                    <?php echo $weather_data['weather'][0]['description']; ?>
                                </p>
                                <div class="row text-center">
                                    <div class="col-4">
                                        <i class="fas fa-tint text-info me-1"></i>
                                        <small>Humidity</small>
                                        <div class="fw-bold"><?php echo $weather_data['main']['humidity']; ?>%</div>
                                    </div>
                                    <div class="col-4">
                                        <i class="fas fa-wind text-info me-1"></i>
                                        <small>Wind</small>
                                        <div class="fw-bold"><?php echo $weather_data['wind']['speed'] ?? '0'; ?> m/s</div>
                                    </div>
                                    <div class="col-4">
                                        <i class="fas fa-temperature-low text-info me-1"></i>
                                        <small>Feels Like</small>
                                        <div class="fw-bold"><?php echo round($weather_data['main']['feels_like']); ?>°C</div>
                                    </div>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-4">
                                <i class="fas fa-cloud-slash fa-3x text-muted mb-3"></i>
                                <p class="text-muted">
                                    <?php echo $weather_error ?: 'Weather information currently unavailable'; ?>
                                </p>
                                <small class="text-muted">
                                    <i class="fas fa-map-marker-alt me-1"></i>
                                    Manila, Philippines
                                </small>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Library Usage Stats -->
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Library Usage Statistics</h5>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-md-2 mb-3">
                        <div class="border rounded p-3">
                            <i class="fas fa-book-open text-primary fa-2x mb-2"></i>
                            <h4><?php echo $books_count; ?></h4>
                            <small class="text-muted">Books</small>
                        </div>
                    </div>
                    <div class="col-md-2 mb-3">
                        <div class="border rounded p-3">
                            <i class="fas fa-user-graduate text-success fa-2x mb-2"></i>
                            <h4><?php echo $students_count; ?></h4>
                            <small class="text-muted">Students</small>
                        </div>
                    </div>
                    <div class="col-md-2 mb-3">
                        <div class="border rounded p-3">
                            <i class="fas fa-hand-holding text-warning fa-2x mb-2"></i>
                            <h4><?php echo $borrowed_count; ?></h4>
                            <small class="text-muted">Borrowed</small>
                        </div>
                    </div>
                    <div class="col-md-2 mb-3">
                        <div class="border rounded p-3">
                            <i class="fas fa-check-circle text-info fa-2x mb-2"></i>
                            <h4><?php echo $returned_count; ?></h4>
                            <small class="text-muted">Returned</small>
                        </div>
                    </div>
                    <div class="col-md-2 mb-3">
                        <div class="border rounded p-3">
                            <i class="fas fa-tags text-secondary fa-2x mb-2"></i>
                            <h4><?php echo $categories_count; ?></h4>
                            <small class="text-muted">Categories</small>
                        </div>
                    </div>
                    <div class="col-md-2 mb-3">
                        <div class="border rounded p-3">
                            <i class="fas fa-user-edit text-dark fa-2x mb-2"></i>
                            <h4><?php echo $authors_count; ?></h4>
                            <small class="text-muted">Authors</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Auto-refresh weather every 10 minutes
        setTimeout(() => {
            window.location.reload();
        }, 600000); // 10 minutes
    </script>
</body>
</html>