<?php
header('Content-Type: application/json');
require_once 'config.php';

$method = $_SERVER['REQUEST_METHOD'];
$id     = isset($_GET['id']) ? (int)$_GET['id'] : null;
$input  = json_decode(file_get_contents('php://input'), true) ?? [];

function respond($data, $code = 200) {
    http_response_code($code);
    echo json_encode($data);
    exit;
}

function exists($pdo, $id) {
    $s = $pdo->prepare('SELECT id FROM profiles WHERE id = ?');
    $s->execute([$id]);
    return (bool)$s->fetch();
}

try {
    switch ($method) {

        // READ (all or one)
        case 'GET':
            if ($id) {
                $s = $pdo->prepare('SELECT * FROM profiles WHERE id = ?');
                $s->execute([$id]);
                $row = $s->fetch();
                if (!$row) respond(['error' => 'Profile not found'], 404);
                respond($row);
            }
            // SEARCH: ?search=word matches name, tagline, location, email or services
            $search = trim($_GET['search'] ?? '');
            if ($search !== '') {
                $like = '%' . $search . '%';
                $s = $pdo->prepare('SELECT * FROM profiles
                    WHERE name LIKE ? OR tagline LIKE ? OR location LIKE ? OR email LIKE ? OR services LIKE ?
                    ORDER BY id DESC');
                $s->execute([$like, $like, $like, $like, $like]);
                respond($s->fetchAll());
            }
            respond($pdo->query('SELECT * FROM profiles ORDER BY id DESC')->fetchAll());

        // CREATE
        case 'POST':
            if (empty(trim($input['name'] ?? ''))) respond(['error' => 'Name is required'], 400);
            $s = $pdo->prepare('INSERT INTO profiles (name, tagline, location, email, services) VALUES (?, ?, ?, ?, ?)');
            $s->execute([
                trim($input['name']),
                trim($input['tagline'] ?? ''),
                trim($input['location'] ?? ''),
                trim($input['email'] ?? ''),
                trim($input['services'] ?? ''),
            ]);
            respond(['message' => 'Profile created', 'id' => $pdo->lastInsertId()], 201);

        // UPDATE
        case 'PUT':
            if (!$id) respond(['error' => 'ID is required'], 400);
            if (!exists($pdo, $id)) respond(['error' => 'Profile not found'], 404);
            if (empty(trim($input['name'] ?? ''))) respond(['error' => 'Name is required'], 400);
            $s = $pdo->prepare('UPDATE profiles SET name=?, tagline=?, location=?, email=?, services=? WHERE id=?');
            $s->execute([
                trim($input['name']),
                trim($input['tagline'] ?? ''),
                trim($input['location'] ?? ''),
                trim($input['email'] ?? ''),
                trim($input['services'] ?? ''),
                $id,
            ]);
            respond(['message' => 'Profile updated']);

        // DELETE
        case 'DELETE':
            if (!$id) respond(['error' => 'ID is required'], 400);
            if (!exists($pdo, $id)) respond(['error' => 'Profile not found'], 404);
            $pdo->prepare('DELETE FROM profiles WHERE id = ?')->execute([$id]);
            respond(['message' => 'Profile deleted']);

        default:
            respond(['error' => 'Method not allowed'], 405);
    }
} catch (PDOException $e) {
    respond(['error' => 'Database error'], 500);
}
