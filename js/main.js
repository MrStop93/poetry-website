// تفعيل القوائم المنسدلة
document.addEventListener('DOMContentLoaded', function() {
    // البحث الآلي
    const searchInput = document.querySelector('.search-input');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            // يمكنك إضافة AJAX للبحث الآلي هنا
        });
    }
    
    // إدارة الأحداث في لوحة التحكم
    const sidebarItems = document.querySelectorAll('.sidebar-menu li a');
    sidebarItems.forEach(item => {
        item.addEventListener('click', function() {
            sidebarItems.forEach(i => i.parentElement.classList.remove('active'));
            this.parentElement.classList.add('active');
        });
    });
    
    // التحقق من صحة النماذج
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            const requiredFields = this.querySelectorAll('[required]');
            let valid = true;
            
            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    field.style.borderColor = 'red';
                    valid = false;
                } else {
                    field.style.borderColor = '';
                }
            });
            
            if (!valid) {
                e.preventDefault();
                alert('الرجاء ملء جميع الحقول المطلوبة');
            }
        });
    });
});

// إنشاء AJAX للبحث عن الشعراء والفنانين
function searchEntities(type, query, callback) {
    fetch(`/api/v1/search?type=${type}&q=${encodeURIComponent(query)}`)
        .then(response => response.json())
        .then(data => callback(data))
        .catch(error => console.error('Error:', error));
}