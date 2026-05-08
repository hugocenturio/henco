/**
 * Henco theme — applied as early as possible to avoid a flash of light theme.
 * Looks for stored preference, falls back to OS preference.
 */
(function () {
  const KEY = 'henco-theme';
  const root = document.documentElement;
  const stored = (() => { try { return localStorage.getItem(KEY); } catch (_) { return null; } })();
  const apply = (t) => {
    if (t === 'dark' || t === 'light') {
      root.setAttribute('data-theme', t);
    } else {
      root.removeAttribute('data-theme');
    }
  };
  apply(stored || 'auto');

  window.HencoTheme = {
    set(t) {
      try { localStorage.setItem(KEY, t); } catch (_) {}
      apply(t);
      document.dispatchEvent(new CustomEvent('henco:theme', { detail: { theme: t } }));
    },
    toggle() {
      const current = root.getAttribute('data-theme')
        || (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
      this.set(current === 'dark' ? 'light' : 'dark');
    },
    current() {
      return root.getAttribute('data-theme') || 'auto';
    }
  };

  // Wire any [data-theme-toggle] buttons after DOM ready.
  document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('[data-theme-toggle]').forEach((el) => {
      el.addEventListener('click', (e) => { e.preventDefault(); window.HencoTheme.toggle(); });
    });
  });
})();
