function initializeDataTable(selector) {
    $(selector).DataTable({
        "language": {
            "url": `locales/${currentLocale}.json`
        },
        "paging": true,
        "pageLength": 100,
        "ordering": true,
        "info": true,
        "searching": true,
        "order": [[0, 'desc']],
        "columnDefs": [
            { "orderable": true, "targets": "_all" }
        ]
    });
}