@extends('layouts.platform')
@section('title','Personeelsleden')
@section('page-title','Personeelsleden')
@section('content')
@include('platform.schools.partials.breadcrumbs', [
    'school' => $school,
    'section' => 'Gebruikers'
])
<div class="page-header">
    <h1>{{ $school->name }}</h1>
    <p>Bekijk welke gebruikers aan deze school gekoppeld zijn en welke rol ze hier hebben.</p>
</div>
@include('platform.schools.partials.tabs', ['school' => $school])
<div class="card">
    <div class="su-head">
        <div><h2>Personeelsleden van deze school</h2><p>Direct zoeken en filteren op schoolrol of status</p></div>
        <a class="btn" href="{{ route('platform.users.create') }}">+ Personeelslid toevoegen</a>
    </div>
    <form id="su-filter-form" method="GET" action="{{ route('platform.schools.users.index', $school) }}" class="su-filters" role="search">
        <label>Zoeken
            <input type="search" name="q" value="{{ $search }}" placeholder="Naam of e-mailadres…" autocomplete="off">
        </label>
        <label>Schoolrol
            <select name="role">
                <option value="">Alle rollen</option>
                @foreach($roles as $role)
                    <option value="{{ $role->slug }}" @selected($roleFilter === $role->slug)>{{ $role->name }}</option>
                @endforeach
            </select>
        </label>
        <label>Schoolstatus
            <select name="status">
                <option value="">Alle statussen</option>
                <option value="active" @selected($statusFilter === 'active')>Actief</option>
                <option value="inactive" @selected($statusFilter === 'inactive')>Inactief</option>
            </select>
        </label>
        <button type="button" id="su-clear" class="btn btn-secondary">Wissen</button>
        <button type="submit" class="btn su-fallback-submit">Zoeken</button>
    </form>
    <div id="su-feedback" aria-live="polite"></div>
    <div id="su-results">@include('platform.schools.users.partials.results')</div>
</div>
@endsection
@push('styles')
<style>
.su-head{display:flex;align-items:center;justify-content:space-between;gap:16px;flex-wrap:wrap;margin-bottom:20px}
.su-head h2{margin:0 0 6px;font-size:19px}.su-head p{margin:0;color:#718096;font-size:12px}
.su-filters{display:grid;grid-template-columns:minmax(220px,1.5fr) minmax(160px,1fr) minmax(155px,1fr) auto auto;align-items:end;gap:12px;background:#f7fafb;border:1px solid #e5edf2;padding:17px;border-radius:12px;margin-bottom:18px}
.su-filters label{display:flex;flex-direction:column;gap:7px;font-size:11px;font-weight:800;color:#475d73}
.su-filters input,.su-filters select{height:42px;width:100%;border:1px solid #d5dfe7;background:white;border-radius:8px;padding:0 12px;font:inherit;font-size:12px;color:#172e4d}
.su-filters>.btn{height:42px}.su-fallback-submit{display:none}
.su-table{width:100%;border-collapse:collapse}.su-table th,.su-table td{text-align:left;padding:15px 12px;border-bottom:1px solid #edf0f3;font-size:12px}.su-table th{font-size:11px;color:#718096;text-transform:uppercase;white-space:nowrap}
.su-pill{display:inline-block;padding:7px 11px;border-radius:20px;font-weight:700;font-size:10px}.su-pill.on{background:#e8f7ee;color:#25845b}.su-pill.off{background:#fceeee;color:#b94747}
.su-results-heading{display:flex;gap:7px;align-items:center;margin-bottom:12px;color:#718096;font-size:12px}.su-results-heading strong{color:#142b45;font-size:15px}
#su-feedback:not(:empty){color:#718096;font-size:11px;margin:0 0 10px}.su-loading{opacity:.55}
@media(max-width:1050px){.su-filters{grid-template-columns:repeat(2,minmax(0,1fr))}}@media(max-width:620px){.su-filters{grid-template-columns:1fr}}
</style>
@endpush
@push('scripts')
<script>
(function(){
    const form=document.getElementById('su-filter-form'),results=document.getElementById('su-results'),
          feedback=document.getElementById('su-feedback'),clear=document.getElementById('su-clear');
    if(!form||!window.fetch||!window.AbortController)return;
    let timeout,controller,requestId=0;
    function url(){
        const qs=new URLSearchParams(new FormData(form));
        for(const [key,value] of [...qs.entries()]) if(!String(value).trim())qs.delete(key);
        return form.action+(qs.toString()?'?'+qs.toString():'');
    }
    async function load(target){
        if(controller)controller.abort();
        controller=new AbortController();
        const id=++requestId;
        results.classList.add('su-loading');feedback.textContent='Resultaten bijwerken…';
        try{
            const response=await fetch(target,{headers:{'X-Requested-With':'XMLHttpRequest','Accept':'text/html'},credentials:'same-origin',signal:controller.signal});
            if(!response.ok)throw new Error('HTTP '+response.status);
            const html=await response.text();
            if(id!==requestId)return;
            results.innerHTML=html;history.replaceState(null,'',target);feedback.textContent='';
        }catch(error){if(error.name!=='AbortError'&&id===requestId)feedback.textContent='Zoeken is niet gelukt. Probeer opnieuw.';}
        finally{if(id===requestId)results.classList.remove('su-loading');}
    }
    function queue(){clearTimeout(timeout);timeout=setTimeout(()=>load(url()),250);}
    form.addEventListener('input',e=>{if(e.target.type==='search')queue();});
    form.addEventListener('change',queue);
    form.addEventListener('submit',e=>{e.preventDefault();clearTimeout(timeout);load(url());});
    clear.addEventListener('click',()=>{form.reset();queue();});
    results.addEventListener('click',e=>{const link=e.target.closest('a[href]');if(!link||!link.closest('nav'))return;e.preventDefault();load(link.href);});
})();
</script>
@endpush
