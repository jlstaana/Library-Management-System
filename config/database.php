<?php
$host = 'localhost';
$dbname = 'library_system';
$username = 'root';
$password = '123';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Error handling function
function handleDbError($e) {
    if ($e->getCode() == 23000) { // Duplicate entry error code
        return "This record already exists in the database.";
    } else {
        return "Database error: " . $e->getMessage();
    }
}
?>