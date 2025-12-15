// Global fallback for deleteCost to avoid ReferenceError
window.deleteCost = window.deleteCost || function(id){
  if (!id) return;
  if (!confirm('Delete this cost?')) return;
  var token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
  fetch('/order-cost/' + id, {
    method: 'DELETE',
    headers: {
      'X-CSRF-TOKEN': token,
      'Accept': 'application/json',
      'Content-Type': 'application/json'
    }
  }).then(function(res){
    if (res.ok) return res.json().catch(function(){return {}});
    throw new Error('Delete failed');
  }).then(function(){
    location.reload();
  }).catch(function(){
    alert('Delete failed');
  });
};
