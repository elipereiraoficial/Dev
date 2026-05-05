<?php
/**
 * Property Image Upload API
 */

header('Content-Type: application/json');
header('Cache-Control: no-store');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit;
}

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/auth.php';

if (empty($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Não autenticado']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Método não permitido']);
    exit;
}

$property_id = intval($_POST['property_id'] ?? 0);
if (!$property_id) {
    echo json_encode(['success' => false, 'error' => 'ID do imóvel não especificado']);
    exit;
}

if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['success' => false, 'error' => 'Erro ao carregar imagem']);
    exit;
}

$file = $_FILES['image'];
$allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
$max_size = 5 * 1024 * 1024; // 5MB

if (!in_array($file['type'], $allowed_types)) {
    echo json_encode(['success' => false, 'error' => 'Tipo de ficheiro não permitido. Use JPEG, PNG, GIF ou WebP']);
    exit;
}

if ($file['size'] > $max_size) {
    echo json_encode(['success' => false, 'error' => 'Ficheiro muito grande. Máximo 5MB']);
    exit;
}

$upload_dir = __DIR__ . '/../uploads/properties/';
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}

$extension = pathinfo($file['name'], PATHINFO_EXTENSION);
$filename = uniqid('prop_') . '_' . time() . '.' . strtolower($extension);
$target_path = $upload_dir . $filename;

if (move_uploaded_file($file['tmp_name'], $target_path)) {
    $stmt = $pdo->prepare("INSERT INTO property_images (property_id, filename, original_name, file_path, file_size, mime_type) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        $property_id,
        $filename,
        $file['name'],
        'uploads/properties/' . $filename,
        $file['size'],
        $file['type']
    ]);
    
    $image_id = $pdo->lastInsertId();
    
    $is_primary = $pdo->query("SELECT COUNT(*) FROM property_images WHERE property_id = $property_id AND is_primary = 1")->fetchColumn();
    if ($is_primary == 0) {
        $pdo->prepare("UPDATE property_images SET is_primary = 1 WHERE id = ?")->execute([$image_id]);
    }
    
    echo json_encode([
        'success' => true,
        'image_id' => $image_id,
        'filename' => $filename,
        'path' => 'uploads/properties/' . $filename
    ]);
    exit;
}

echo json_encode(['success' => false, 'error' => 'Erro ao guardar ficheiro']);