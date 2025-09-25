<?php
header('Content-Type: application/json');
require_once '../config/database.php';

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        if (isset($_GET['id'])) {
            // Get single student
            $stmt = $pdo->prepare("SELECT * FROM students WHERE Student_ID = ?");
            $stmt->execute([$_GET['id']]);
            $student = $stmt->fetch(PDO::FETCH_ASSOC);
            echo json_encode($student);
        } else {
            // Get all students
            $students = $pdo->query("SELECT * FROM students")->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode($students);
        }
        break;
        
    case 'POST':
        // Create new student
        $data = json_decode(file_get_contents('php://input'), true);
        $stmt = $pdo->prepare("INSERT INTO students (Name, Email, Username, Password) VALUES (?, ?, ?, ?)");
        $stmt->execute([$data['name'], $data['email'], $data['username'], password_hash($data['password'], PASSWORD_DEFAULT)]);
        echo json_encode(['message' => 'Student created successfully']);
        break;
        
    case 'PUT':
        // Update student
        $data = json_decode(file_get_contents('php://input'), true);
        $stmt = $pdo->prepare("UPDATE students SET Name=?, Email=?, Username=? WHERE Student_ID=?");
        $stmt->execute([$data['name'], $data['email'], $data['username'], $data['id']]);
        echo json_encode(['message' => 'Student updated successfully']);
        break;
        
    case 'DELETE':
        // Delete student
        $data = json_decode(file_get_contents('php://input'), true);
        $stmt = $pdo->prepare("DELETE FROM students WHERE Student_ID=?");
        $stmt->execute([$data['id']]);
        echo json_encode(['message' => 'Student deleted successfully']);
        break;
}
?>