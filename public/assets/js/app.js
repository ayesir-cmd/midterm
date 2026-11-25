const menuButton=document.querySelector('#menuButton');
const sidebar=document.querySelector('#sidebar');
if(menuButton&&sidebar){
  menuButton.addEventListener('click',()=>{const open=sidebar.classList.toggle('open');menuButton.setAttribute('aria-expanded',String(open));});
  sidebar.querySelectorAll('a').forEach(link=>link.addEventListener('click',()=>sidebar.classList.remove('open')));
}
document.querySelectorAll('.nav-group-toggle').forEach(button=>{
  const menu=document.getElementById(button.getAttribute('aria-controls'));
  if(!menu)return;
  button.addEventListener('click',()=>{
    const expanded=button.getAttribute('aria-expanded')==='true';
    button.setAttribute('aria-expanded',String(!expanded));
    menu.hidden=expanded;
  });
});
const toggle=document.querySelector('#togglePassword');
if(toggle){toggle.addEventListener('click',()=>{const input=document.querySelector('#password');const show=input.type==='password';input.type=show?'text':'password';toggle.textContent=show?'Hide':'Show';toggle.setAttribute('aria-label',show?'Hide password':'Show password');});}
if('serviceWorker' in navigator){window.addEventListener('load',()=>navigator.serviceWorker.register('/sw.js').catch(()=>{}));}
