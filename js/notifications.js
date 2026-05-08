document.addEventListener('DOMContentLoaded', function () {
    const notificationIcon = document.getElementById('notificationIcon');
    const notificationDropdown = document.getElementById('notificationDropdown');
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';

    // Marcar notificações como lidas ao abrir o popup
    notificationIcon.addEventListener('click', function () {
        const body = 'mark_as_read=1&csrf_token=' + encodeURIComponent(csrfToken);
        fetch('notifications.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: body
        }).then(response => response.json())
          .then(data => {
              if (data.success) {
                  const badge = notificationIcon.querySelector('.badge');
                  if (badge) badge.style.display = 'none';
              }
          });
    });
});
