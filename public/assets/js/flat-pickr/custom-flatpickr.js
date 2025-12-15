(function(){
  function initFlatpickr() {
    if (typeof flatpickr === 'undefined') return;
    document.querySelectorAll('.flatpickr').forEach(function(el){
      try {
        if (!el._fp) {
          flatpickr(el, {});
          el._fp = true;
        }
      } catch(e){
        console.warn('flatpickr init failed', e);
      }
    });
  }
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initFlatpickr);
  } else {
    initFlatpickr();
  }
})();
