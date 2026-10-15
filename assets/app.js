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

document.querySelectorAll('[data-copy-secret]').forEach(button=>button.addEventListener('click',async()=>{try{await navigator.clipboard.writeText(document.getElementById(button.dataset.copySecret).textContent);button.textContent='Copied';}catch{button.textContent='Select the password to copy';}}));

// Action1 batches persist server-side, so closing the page can be resumed later.
(() => {
 let busy=false, stopped=false;
 const progress=document.getElementById('action1-progress'), stop=document.getElementById('action1-stop');
 if(!progress||!stop)return;
 stop.addEventListener('click',()=>{stopped=true;stop.disabled=true;});
 const wait=ms=>new Promise(resolve=>setTimeout(resolve,ms));
 document.querySelectorAll('form[data-action1-run]').forEach(form=>form.addEventListener('submit',async event=>{
  event.preventDefault();if(busy)return;busy=true;stopped=false;progress.hidden=false;stop.hidden=false;stop.disabled=false;
  const button=form.querySelector('button');button.disabled=true;let target=form.action,attempts=0;
  try {
   while(!stopped){
    progress.textContent='Contacting Action1…';const response=await fetch(target,{method:'POST',body:new URLSearchParams(new FormData(form)),headers:{Accept:'application/json'},credentials:'same-origin'});
    if(!response.headers.get('content-type')?.includes('application/json'))throw new Error('Your session may have expired. Reload this page and sign in again.');
    const result=await response.json();progress.textContent=result.message||'Batch processed.';
    if(response.status===429&&Number(result.retry_after)>0){if(++attempts>30)throw new Error('Action1 is still unavailable. Resume this operation later.');const seconds=Math.max(1,Math.min(86400,Number(result.retry_after)));progress.textContent+=' Waiting '+seconds+' seconds.';for(let remaining=seconds;remaining>0&&!stopped;remaining--)await wait(1000);continue;}
    if(!response.ok)throw new Error(result.message||'Sync could not complete.');attempts=0;
    if(!result.more){if(form.dataset.reload)location.reload();break;}
    if(!form.dataset.next)throw new Error('No continuation step is available. Reload to resume.');target=form.dataset.next;
    for(let remaining=Math.max(1,Number(result.retry_after)||3);remaining>0&&!stopped;remaining--)await wait(1000);
   }
   if(stopped)progress.textContent='Stopped after the current batch. Use Resume sync or Continue loading organizations to continue.';
  }catch(error){progress.textContent=error.message+' No completed batch was discarded. Reload the page to see status and resume.';}
  finally{busy=false;stop.hidden=true;button.disabled=false;}
 }));
})();

// Native details keeps the column picker usable without JavaScript.
document.querySelectorAll('.column-settings').forEach(picker=>{
 document.addEventListener('click',event=>{if(picker.open&&!picker.contains(event.target))picker.open=false;});
 picker.addEventListener('keydown',event=>{if(event.key==='Escape'){picker.open=false;picker.querySelector('summary').focus();}});
});
