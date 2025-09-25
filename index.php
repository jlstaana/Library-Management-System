<?php
// Enable full error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<!-- PHP is starting -->";

session_start();
echo "<!-- Session started -->";

try {
    require_once 'config/database.php';
    echo "<!-- Database config loaded -->";
} catch (Exception $e) {
    die("<div style='background: red; color: white; padding: 20px;'>DATABASE ERROR: " . $e->getMessage() . "</div>");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Library System Debug</title>
    <link rel="icon" type="image/jpg" href="\Lab_Exam_LibSys\logo.jpg">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
</head>
<body style="background-color: yellow;">
    <div style="background: red; color: white; padding: 10px;">
        <strong>DEBUG MODE:</strong> If you see this, PHP is working!
    </div>

    <?php
    echo "<!-- Including navbar.php -->";
    if (file_exists('navbar.php')) {
        include 'navbar.php';
        echo "<!-- navbar.php included successfully -->";
    } else {
        echo "<div style='background: darkred; color: white; padding: 10px;'>ERROR: navbar.php not found!</div>";
    }
    ?>

    <div class="container mt-4" style="border: 3px solid blue;">
        <h1 style="color: green;">Welcome to Library System - DEBUG</h1>
        <p style="color: green;">If you can see this text, the page is loading correctly.</p>
        
        <div class="alert alert-success">
            <h4>✓ PHP is working!</h4>
            <p>Current time: <?php echo date('Y-m-d H:i:s'); ?></p>
        </div>

        <div class="alert alert-info">
            <h4>File Check:</h4>
            <ul>
                <li>navbar.php: <?php echo file_exists('navbar.php') ? '✓ EXISTS' : '✗ MISSING'; ?></li>
                <li>config/database.php: <?php echo file_exists('config/database.php') ? '✓ EXISTS' : '✗ MISSING'; ?></li>
                <li>css/style.css: <?php echo file_exists('css/style.css') ? '✓ EXISTS' : '✗ MISSING'; ?></li>
            </ul>
        </div>

        <div class="alert alert-warning">
            <h4>Database Test:</h4>
            <?php
            try {
                $test = $pdo->query("SELECT 1");
                echo "<p style='color: green;'>✓ Database connection successful!</p>";
            } catch (Exception $e) {
                echo "<p style='color: red;'>✗ Database error: " . $e->getMessage() . "</p>";
            }
            ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>