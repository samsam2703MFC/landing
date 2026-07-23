/* Compat redirect — the office app moved to /landing/office/.
   Some stale/externally-generated HTML at the old root URL still loads
   "livraison-bureau.jsx". Keeping this tiny redirect here means that even
   when that happens, the visitor is sent to the real page instead of a
   blank/broken screen. The real app lives in /landing/office/. */
;(function () {
  try {
    location.replace('/landing/office/livraison-bureau.html' + (location.hash || ''));
  } catch (e) {
    if (typeof document !== 'undefined') {
      document.title = 'Livraison Bureau';
    }
  }
})();
