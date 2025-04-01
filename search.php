<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';

$search_query = isset($_GET['q']) ? $db->sanitize($_GET['q']) : '';
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>بحث - النظام الشعري</title>
    <style>
        .search-container {
            max-width: 800px;
            margin: 30px auto;
        }
        .search-box {
            position: relative;
            margin-bottom: 30px;
        }
        .search-input {
            width: 100%;
            padding: 12px 20px;
            font-size: 18px;
            border: 2px solid #3498db;
            border-radius: 30px;
        }
        .search-results {
            position: absolute;
            width: 100%;
            background: white;
            border: 1px solid #ddd;
            border-top: none;
            z-index: 1000;
            display: none;
        }
        .search-item {
            padding: 10px 15px;
            cursor: pointer;
            display: flex;
            align-items: center;
        }
        .search-item:hover {
            background: #f5f5f5;
        }
        .search-item img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            margin-left: 10px;
        }
        .search-type {
            background: #3498db;
            color: white;
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 12px;
            margin-right: 10px;
        }
    </style>
</head>
<body>
    <div class="search-container">
        <div class="search-box">
            <input type="text" id="search-input" class="search-input" 
                   placeholder="ابحث عن شاعر، قصيدة أو فنان..." 
                   value="<?= safe_output($search_query) ?>" autocomplete="off">
            <div id="search-results" class="search-results"></div>
        </div>

        <div id="full-results">
            <!-- سيتم عرض النتائج الكاملة هنا -->
        </div>
    </div>

    <script>
    document.getElementById('search-input').addEventListener('input', function() {
        const query = this.value.trim();
        const resultsContainer = document.getElementById('search-results');
        
        if (query.length >= 2) {
            fetch(`api/search.php?q=${encodeURIComponent(query)}`)
                .then(response => response.json())
                .then(data => {
                    resultsContainer.innerHTML = '';
                    if (data.length > 0) {
                        data.slice(0, 4).forEach(item => {
                            const div = document.createElement('div');
                            div.className = 'search-item';
                            div.innerHTML = `
                                ${item.type ? `<span class="search-type">${item.type}</span>` : ''}
                                ${item.image ? `<img src="${item.image}" alt="${item.name}">` : ''}
                                <span>${item.name}</span>
                            `;
                            div.addEventListener('click', () => {
                                window.location.href = item.link;
                            });
                            resultsContainer.appendChild(div);
                        });
                        resultsContainer.style.display = 'block';
                    } else {
                        resultsContainer.style.display = 'none';
                    }
                });
        } else {
            resultsContainer.style.display = 'none';
        }
    });

    // البحث عند الضغط على Enter
    document.getElementById('search-input').addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            window.location.href = `search.php?q=${encodeURIComponent(this.value)}`;
        }
    });
    </script>
</body>
</html>