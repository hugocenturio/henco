/**
 * Notifications — mark-as-read on dropdown open, plus a light badge poller.
 */
(function () {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
    const baseUrl   = (document.querySelector('meta[name="base-url"]')?.content || '/').replace(/\/$/, '');

    const icon   = document.getElementById('notificationIcon');
    const badge  = document.getElementById('notificationBadge');
    const heading = document.getElementById('notificationDropdownHeading');

    function setCount(count) {
        if (!badge) return;
        if (count > 0) {
            badge.textContent = count;
            badge.classList.remove('d-none');
        } else {
            badge.classList.add('d-none');
        }
        if (heading) {
            heading.textContent = count === 1 ? '1 new notification' : count + ' new notifications';
        }
    }

    if (icon) {
        icon.addEventListener('click', function () {
            fetch(baseUrl + '/notifications/read', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'Accept': 'application/json' },
                body: 'csrf_token=' + encodeURIComponent(csrfToken),
            })
            .then(r => r.ok ? r.json() : null)
            .then(data => { if (data?.success) setCount(0); })
            .catch(() => {});
        });
    }

    function poll() {
        fetch(baseUrl + '/notifications/count', { headers: { 'Accept': 'application/json' } })
            .then(r => r.ok ? r.json() : null)
            .then(data => {
                if (data && typeof data.count === 'number') setCount(data.count);
            })
            .catch(() => {});
    }

    // Poll every 60s while the tab is visible.
    let interval = null;
    function start() {
        if (interval) return;
        interval = setInterval(() => { if (!document.hidden) poll(); }, 60000);
    }
    function stop() {
        if (!interval) return;
        clearInterval(interval); interval = null;
    }
    document.addEventListener('visibilitychange', () => document.hidden ? stop() : start());
    document.addEventListener('DOMContentLoaded', start);
})();
