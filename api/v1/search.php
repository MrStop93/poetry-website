<?php
require_once '../../includes/db.php';
require_once '../../includes/functions.php';

header('Content-Type: application/json');

$query = $_GET['q'] ?? '';
$results = [];

if (strlen($query) >= 2) {
    // البحث في الشعراء (الأولوية للمطابقة في البداية)
    $stmt = $db->prepare("
        SELECT id, name, 'شاعر' AS type, image 
        FROM poets 
        WHERE name LIKE ? OR name LIKE ?
        ORDER BY 
            CASE 
                WHEN name LIKE ? THEN 1 
                ELSE 2 
            END,
            name ASC
        LIMIT 2
    ");
    $startWith = "$query%";
    $contains = "%$query%";
    $stmt->bind_param("sss", $startWith, $contains, $startWith);
    $stmt->execute();
    $poets = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    foreach ($poets as $poet) {
        $results[] = [
            'name' => $poet['name'],
            'type' => $poet['type'],
            'image' => !empty($poet['image']) ? "/uploads/poets/{$poet['image']}" : null,
            'link' => "poet.php?id=" . $poet['id']
        ];
    }

    // البحث في القصائد
    $stmt = $db->prepare("
        SELECT p.id, p.title AS name, 'قصيدة' AS type, pt.image AS poet_image
        FROM poems p
        JOIN poets pt ON p.poet_id = pt.id
        WHERE p.title LIKE ? OR p.title LIKE ?
        ORDER BY 
            CASE 
                WHEN p.title LIKE ? THEN 1 
                ELSE 2 
            END,
            p.title ASC
        LIMIT 2
    ");
    $stmt->bind_param("sss", $startWith, $contains, $startWith);
    $stmt->execute();
    $poems = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    foreach ($poems as $poem) {
        $results[] = [
            'name' => $poem['name'],
            'type' => $poem['type'],
            'image' => !empty($poem['poet_image']) ? "/uploads/poets/{$poem['poet_image']}" : null,
            'link' => "poem.php?id=" . $poem['id']
        ];
    }

    // البحث في الفنانين
    $stmt = $db->prepare("
        SELECT id, name, 'فنان' AS type, image 
        FROM artists 
        WHERE name LIKE ? OR name LIKE ?
        ORDER BY 
            CASE 
                WHEN name LIKE ? THEN 1 
                ELSE 2 
            END,
            name ASC
        LIMIT 2
    ");
    $stmt->bind_param("sss", $startWith, $contains, $startWith);
    $stmt->execute();
    $artists = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    foreach ($artists as $artist) {
        $results[] = [
            'name' => $artist['name'],
            'type' => $artist['type'],
            'image' => !empty($artist['image']) ? "/uploads/artists/{$artist['image']}" : null,
            'link' => "artist.php?id=" . $artist['id']
        ];
    }
}

echo json_encode($results);
?>