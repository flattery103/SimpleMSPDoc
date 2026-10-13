'use strict';
document.querySelectorAll('[data-org-switch]').forEach(select => select.addEventListener('change',()=>{if(select.value)location.href=select.dataset.base+encodeURIComponent(select.value);}));
document.querySelectorAll('[data-submit-change]').forEach(select=>select.addEventListener('change',()=>select.form.requestSubmit()));
document.querySelectorAll('form[data-confirm]').forEach(form=>form.addEventListener('submit',event=>{if(!confirm(form.dataset.confirm))event.preventDefault();}));
document.querySelectorAll('.sidebar nav a').forEach(a=>{if(location.pathname===new URL(a.href).pathname&&new URL(location.href).searchParams.get('route')===new URL(a.href).searchParams.get('route'))a.classList.add('active');});
document.querySelectorAll('[data-rich-editor]').forEach(editor=>{
 const surface=editor.querySelector('.editor-surface');const source=editor.querySelector('.html-source');editor.classList.add('enhanced');let sourceMode=false;
 editor.querySelectorAll('[data-command]').forEach(button=>{button.addEventListener('mousedown',event=>event.preventDefault());button.addEventListener('click',()=>{surface.focus();let value=button.dataset.value??null;if(button.dataset.command==='createLink'){value=prompt('Link URL (https:// or http://)');if(!value||!/^https?:\/\//i.test(value))return;}document.execCommand(button.dataset.command,false,value);});});
 surface.addEventListener('paste',event=>{event.preventDefault();document.execCommand('insertText',false,event.clipboardData.getData('text/plain'));});
 editor.querySelector('.source-toggle').addEventListener('click',()=>{if(!sourceMode){source.value=surface.innerHTML;sourceMode=true;editor.classList.add('source-mode');editor.querySelector('.source-toggle').textContent='HTML source mode — save to apply formatting';source.focus();}else{source.focus();}});
 editor.closest('form').addEventListener('submit',()=>{if(!sourceMode)source.value=surface.innerHTML;});
});
