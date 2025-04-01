<?php
require_once '../../includes/config.php';
require_once '../../includes/db.php';

header('Content-Type: application/json');

// التحقق من API Key
if (!isset($_SERVER['HTTP_X_API_KEY']) || $_SERVER['HTTP_X_API_KEY'] !== API_KEY) {
    http_response_code(401);
    die(json_encode(['error' => 'Unauthorized']));
}

$type = $_GET['type'] ?? 'all';
$query = $_GET['q'] ?? '';

try {
    $results = [];
    
    switch ($type) {
        case 'poets':
            $stmt = $db->prepare("SELECT id, name FROM poets WHERE name LIKE ? LIMIT 10");
            $search_term = "%$query%";
            $stmt->bind_param("s", $search_term);
            $stmt->execute();
            $result = $stmt->get_result();
            while ($row = $result->fetch_assoc()) {
                $results[] = $row;
            }
            break;
            
        case 'artists':
            $stmt = $db->prepare("SELECT id, name FROM artists WHERE name LIKE ? LIMIT 10");
            $search_term = "%$query%";
            $stmt->bind_param("s", $search_term);
            $stmt->execute();
            $result = $stmt->get_result();
            while ($row = $result->fetch_assoc()) {
                $results[] = $row;
            }
            break;
            
        default:
            http_response_code(400);
            die(json_encode(['error' => 'Invalid search type']));
    }
    
    echo json_encode(['success' => true, 'data' => $results]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>