(function(){
  // Load the real flatpickr from libs if not already loaded
  if (typeof window.flatpickr === 'undefined') {
    var s = document.createElement('script');
    s.src = '/assets/libs/flatpickr/flatpickr.min.js';
    s.defer = true;
    s.onload = function(){ /* noop */ };
    document.head.appendChild(s);
  }
})();
