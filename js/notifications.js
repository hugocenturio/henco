document.addEventListener('DOMContentLoaded', function () {
    const notificationIcon = document.getElementById('notificationIcon');
    const notificationDropdown = document.getElementById('notificationDropdown');

    // Marcar notificações como lidas ao abrir o popup
    notificationIcon.addEventListener('click', function () {
        fetch('notifications.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'mark_as_read=1'
        }).then(response => response.json())
          .then(data => {
              if (data.success) {
                  const badge = notificationIcon.querySelector('.badge');
                  if (badge) badge.style.display = 'none';
              }
          });
    });
});
