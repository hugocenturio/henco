document.addEventListener('DOMContentLoaded', function () {
    const categoryTags = document.querySelectorAll('.category-tag');
    const tableRows = document.querySelectorAll('#Data_Table_0 tbody tr');
    let selectedCategories = new Set();

    categoryTags.forEach(tag => {
        tag.addEventListener('click', () => {
            const category = tag.dataset.category;

            if (selectedCategories.has(category)) {
                selectedCategories.delete(category);
                tag.classList.remove('bg-warning');
                tag.classList.add('bg-primary');
            } else {
                selectedCategories.add(category);
                tag.classList.remove('bg-primary');
                tag.classList.add('bg-warning');
            }

            filterTable();
        });
    });

    function filterTable() {
        const categories = Array.from(selectedCategories);

        tableRows.forEach(row => {
            const rowCategory = row.querySelector('td:first-child').textContent.trim();

            if (categories.length === 0 || categories.includes(rowCategory)) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }
});
