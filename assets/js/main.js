// Small helper: show toast notifications
function showToast(message, type='primary'){
  const id = 't'+Date.now();
  const wrap = document.querySelector('.toast-wrap');
  if(!wrap) return;
  const div = document.createElement('div');
  div.className = 'toast align-items-center text-bg-'+type+' border-0 show mb-2';
  div.id = id;
  div.setAttribute('role','alert');
  div.setAttribute('aria-live','assertive');
  div.setAttribute('aria-atomic','true');
  div.innerHTML = `<div class='d-flex'><div class='toast-body'>${message}</div><button type='button' class='btn-close btn-close-white me-2 m-auto' data-bs-dismiss='toast' aria-label='Close'></button></div>`;
  wrap.appendChild(div);
  setTimeout(()=>{ try{ div.remove(); }catch(e){} },4000);
}
// Auto show flash messages from server
document.addEventListener('DOMContentLoaded',()=>{
  const flash = document.getElementById('server-flash');
  if(flash && flash.dataset.msg){ showToast(flash.dataset.msg, 'primary'); }
});
