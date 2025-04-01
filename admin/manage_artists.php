<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';

if (!Auth::check()) {
    header("Location: login.php");
    exit();
}

// معالجة ربط الفنانين بالقصائد
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['link_artist'])) {
    $csrf_token = $_POST['csrf_token'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'], $csrf_token)) {
        die("Invalid CSRF token");
    }

    $poem_id = (int)$_POST['poem_id'];
    $artist_id = (int)$_POST['artist_id'];
    
    // حذف الربط القديم أولاً
    $db->query("DELETE FROM poem_artist WHERE poem_id = $poem_id");
    
    // إضافة الربط الجديد
    $stmt = $db->prepare("INSERT INTO poem_artist (poem_id, artist_id) VALUES (?, ?)");
    $stmt->bind_param("ii", $poem_id, $artist_id);
    $stmt->execute();
}
?>

<!-- واجهة ربط الفنانين -->
<script>
// AJAX للبحث عن الفنانين
function searchArtists(query) {
    fetch(`search_artists.php?q=${query}`)
        .then(response => response.json())
        .then(data => {
            const results = document.getElementById('artist-results');
            results.innerHTML = '';
            data.forEach(artist => {
                const div = document.createElement('div');
                div.innerHTML = `
                    <input type="radio" name="artist_id" value="${artist.id}" id="artist_${artist.id}">
                    <label for="artist_${artist.id}">${artist.name} (${artist.country})</label>
                `;
                results.appendChild(div);
            });
        });
}
</script>

<form method="POST">
    <input type="hidden" name="csrf_token" value="<?= Auth::generateCSRFToken() ?>">
    <input type="hidden" name="poem_id" value="<?= $_GET['poem_id'] ?>">
    
    <div class="form-group">
        <label>بحث عن فنان:</label>
        <input type="text" oninput="searchArtists(this.value)">
        <div id="artist-results"></div>
    </div>
    
    <button type="submit" name="link_artist">ربط الفنان</button>
</form>