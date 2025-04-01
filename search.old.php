<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';

$search_query = isset($_GET['q']) ? $db->sanitize($_GET['q']) : '';
$search_type = isset($_GET['type']) ? $_GET['type'] : 'all';
$tag_filter = isset($_GET['tag']) ? $db->sanitize($_GET['tag']) : '';

// بناء استعلام البحث
$where = [];
$join = [];

if (!empty($search_query)) {
    switch ($search_type) {
        case 'poems':
            $where[] = "(p.title LIKE '%$search_query%' OR p.content LIKE '%$search_query%')";
            break;
        case 'poets':
            $where[] = "pt.name LIKE '%$search_query%'";
            break;
        case 'artists':
            $join[] = "LEFT JOIN poem_artist pa ON p.id = pa.poem_id";
            $join[] = "LEFT JOIN artists a ON pa.artist_id = a.id";
            $where[] = "a.name LIKE '%$search_query%'";
            break;
        case 'composers':
            $join[] = "LEFT JOIN poem_composer pc ON p.id = pc.poem_id";
            $join[] = "LEFT JOIN composers c ON pc.composer_id = c.id";
            $where[] = "c.name LIKE '%$search_query%'";
            break;
        default:
            $join[] = "LEFT JOIN poem_artist pa ON p.id = pa.poem_id";
            $join[] = "LEFT JOIN artists a ON pa.artist_id = a.id";
            $join[] = "LEFT JOIN poem_composer pc ON p.id = pc.poem_id";
            $join[] = "LEFT JOIN composers c ON pc.composer_id = c.id";
            $where[] = "(p.title LIKE '%$search_query%' OR 
                         p.content LIKE '%$search_query%' OR 
                         pt.name LIKE '%$search_query%' OR 
                         a.name LIKE '%$search_query%' OR
                         c.name LIKE '%$search_query%')";
    }
}

if (!empty($tag_filter)) {
    $join[] = "LEFT JOIN poem_tags ptg ON p.id = ptg.poem_id";
    $join[] = "LEFT JOIN tags t ON ptg.tag_id = t.id";
    $where[] = "t.name = '$tag_filter'";
}

$sql = "SELECT DISTINCT p.id, p.title, p.rhyme, p.verses_count, pt.name AS poet_name
        FROM poems p
        LEFT JOIN poets pt ON p.poet_id = pt.id
        " . implode(" ", $join) . "
        " . (!empty($where) ? " WHERE " . implode(" AND ", $where) : "") . "
        ORDER BY p.title";

$result = $db->query($sql);
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>بحث - النظام الشعري</title>
    <style>
        .search-result {
            margin-bottom: 20px;
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        .search-meta {
            color: #666;
            font-size: 0.9em;
        }
    </style>
</head>
<body>
    <h1>بحث في القصائد</h1>
    
    <form method="GET" action="search.php">
        <input type="text" name="q" value="<?= safe_output($search_query) ?>" placeholder="ابحث عن قصيدة، شاعر، فنان أو ملحن">
        
        <select name="type">
            <option value="all" <?= $search_type === 'all' ? 'selected' : '' ?>>الكل</option>
            <option value="poems" <?= $search_type === 'poems' ? 'selected' : '' ?>>القصائد</option>
            <option value="poets" <?= $search_type === 'poets' ? 'selected' : '' ?>>الشعراء</option>
            <option value="artists" <?= $search_type === 'artists' ? 'selected' : '' ?>>الفنانين</option>
            <option value="composers" <?= $search_type === 'composers' ? 'selected' : '' ?>>الملحنين</option>
        </select>
        
        <button type="submit">بحث</button>
    </form>
    
    <?php if ($result->num_rows > 0): ?>
        <h2>نتائج البحث:</h2>
        <?php while ($row = $result->fetch_assoc()): ?>
            <div class="search-result">
                <h3><a href="poem.php?id=<?= $row['id'] ?>"><?= safe_output($row['title']) ?></a></h3>
                <p class="search-meta">
                    الشاعر: <?= safe_output($row['poet_name']) ?> | 
                    القافية: <?= safe_output($row['rhyme']) ?> | 
                    عدد الأبيات: <?= safe_output($row['verses_count']) ?>
                </p>
            </div>
        <?php endwhile; ?>
    <?php elseif (!empty($search_query) || !empty($tag_filter)): ?>
        <p>لا توجد نتائج مطابقة لبحثك.</p>
    <?php endif; ?>
</body>
</html>