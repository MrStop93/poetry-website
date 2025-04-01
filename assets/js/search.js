document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('search-input');
    const resultsContainer = document.getElementById('search-results');
    let selectedIndex = -1;

    // البحث أثناء الكتابة
    searchInput.addEventListener('input', function() {
        const query = this.value.trim();
        selectedIndex = -1;
        
        if (query.length >= 2) {
            fetch(`/api/search?q=${encodeURIComponent(query)}`)
                .then(handleResponse)
                .then(showResults)
                .catch(handleError);
        } else {
            resultsContainer.style.display = 'none';
        }
    });

    // التنقل بالسهمين
    searchInput.addEventListener('keydown', function(e) {
        const items = resultsContainer.querySelectorAll('.search-item');
        
        if (e.key === 'ArrowDown') {
            selectedIndex = Math.min(selectedIndex + 1, items.length - 1);
            updateSelection(items);
        } else if (e.key === 'ArrowUp') {
            selectedIndex = Math.max(selectedIndex - 1, -1);
            updateSelection(items);
        } else if (e.key === 'Enter' && selectedIndex >= 0) {
            items[selectedIndex].click();
        }
    });

    function handleResponse(response) {
        if (!response.ok) throw new Error('Network error');
        return response.json();
    }

    function showResults(data) {
        resultsContainer.innerHTML = '';
        
        if (data.length > 0) {
            data.slice(0, 4).forEach((item, index) => {
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
    }

    function updateSelection(items) {
        items.forEach((item, index) => {
            item.style.backgroundColor = index === selectedIndex ? '#f0f0f0' : '';
        });
    }

    function handleError(error) {
        console.error('Search error:', error);
        resultsContainer.style.display = 'none';
    }
});