<?php
header('Content-Type: application/json');
require_once '../../includes/config.php';
require_once '../../includes/db.php';

// التحقق من API Key
if (!isset($_SERVER['HTTP_X_API_KEY']) || $_SERVER['HTTP_X_API_KEY'] !== API_KEY) {
    http_response_code(401);
    die(json_encode(['error' => 'Unauthorized']));
}

// تحديد نوع الطلب
$method = $_SERVER['REQUEST_METHOD'];

try {
    switch ($method) {
        case 'GET':
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $limit = 10;
            $offset = ($page - 1) * $limit;
            
            // فلترة النتائج
            $where = [];
            if (isset($_GET['poet_id'])) {
                $where[] = "p.poet_id = " . (int)$_GET['poet_id'];
            }
            
            $where_clause = !empty($where) ? " WHERE " . implode(' AND ', $where) : "";
            
            // جلب القصائد مع بيانات الشاعر
            $sql = "SELECT p.*, pt.name as poet_name 
                    FROM poems p
                    LEFT JOIN poets pt ON p.poet_id = pt.id
                    $where_clause
                    LIMIT $limit OFFSET $offset";
            
            $result = $db->query($sql);
            $poems = [];
            
            while ($row = $result->fetch_assoc()) {
                // جلب الحقول المخصصة
                $custom_fields = $db->query("SELECT field_name, field_value FROM poem_custom_fields WHERE poem_id = {$row['id']}");
                $row['custom_fields'] = [];
                
                while ($field = $custom_fields->fetch_assoc()) {
                    $row['custom_fields'][$field['field_name']] = $field['field_value'];
                }
                
                $poems[] = $row;
            }
            
            echo json_encode([
                'success' => true,
                'data' => $poems,
                'pagination' => [
                    'page' => $page,
                    'limit' => $limit
                ]
            ]);
            break;
            
        default:
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>