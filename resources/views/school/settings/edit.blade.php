@extends('layouts.school')
@section('title','Schoolinstellingen')
@section('page-title','Schoolinstellingen')
@section('content')
<div class="page-header"><h1>Huisstijl en instellingen</h1><p>Personaliseer {{ $school->name }}. Deze wijzigingen gelden uitsluitend voor de actieve academie.</p></div>
@if(session('success'))<div class="si-notice si-success">{{ session('success') }}</div>@endif
@if($errors->any())<div class="si-notice si-error"><strong>Controleer de ingevulde gegevens.</strong>@foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach</div>@endif
<form method="POST" action="{{ route('school.settings.update') }}" enctype="multipart/form-data" class="si-wrap">@csrf @method('PUT')
<div class="card si-card"><h2>Teksten op de loginpagina</h2><p>Wat zien medewerkers wanneer ze via de academie aanmelden?</p>
<div class="si-fields">
<label>Slogan<input name="slogan" maxlength="255" value="{{ old('slogan',$school->settings?->slogan) }}" placeholder="Bijvoorbeeld: Ruimte voor talent."></label>
<label>Ondertitel<input name="subtitle" maxlength="255" value="{{ old('subtitle',$school->settings?->subtitle) }}" placeholder="Bijvoorbeeld: Muziek, woord, dans en beeld"></label>
<label class="si-wide">Beschrijving<textarea name="login_description" maxlength="2000" rows="4" placeholder="Korte tekst voor de aanmeldpagina">{{ old('login_description',$school->settings?->login_description) }}</textarea></label>
</div></div>
<div class="card si-card"><h2>Schoolkleuren</h2><p>De gekozen kleuren worden gebruikt in de schoolspecifieke loginpagina.</p>
<div class="si-colors">
@foreach(['primary_color'=>['Primaire kleur','#142B45'],'secondary_color'=>['Secundaire kleur','#59AAA5'],'accent_color'=>['Accentkleur','#E5A65B']] as $key=>$data)
@php $value=old($key,$school->settings?->{$key} ?? $data[1]); @endphp
<label>{{ $data[0] }}
<div class="si-color-input"><input type="color" value="{{ $value }}" data-hex="{{ $key }}"><input type="text" id="{{ $key }}" name="{{ $key }}" value="{{ $value }}" pattern="#[0-9a-fA-F]{6}" maxlength="7" required></div>
</label>
@endforeach
</div><div class="si-preview" id="si-preview"><strong>{{ $school->name }}</strong><h3 id="si-preview-slogan">{{ old('slogan',$school->settings?->slogan ?? 'Ruimte voor talent.') }}</h3><small id="si-preview-subtitle">{{ old('subtitle',$school->settings?->subtitle ?? '') }}</small><div class="si-preview-lines"><i id="si-accent"></i><i id="si-secondary"></i></div></div>
</div>
<div class="card si-card"><h2>Academielogo</h2><p>Dit logo verschijnt bij het kiezen van de academie en in de schoolomgeving. Het Artelynx-logo blijft de identiteit van het platform.</p>
@if($school->settings?->logo_path)
<div class="si-current"><img src="{{ asset('storage/'.$school->settings->logo_path) }}" alt="Huidig academielogo"><label><input type="checkbox" name="remove_logo" value="1" @checked(old('remove_logo'))> Huidig logo verwijderen</label></div>
@else
<p>Er is nog geen academielogo geüpload.</p>
@endif
<label class="si-file">Logo uploaden of vervangen<input name="logo" type="file" accept=".png,.jpg,.jpeg,.webp,image/png,image/jpeg,image/webp"><small>PNG, JPG of WebP, maximaal 2 MB.</small></label>
</div>
<div class="card si-card"><h2>Algemene schoolinstellingen</h2>
<div class="si-fields"><label>Taal<select name="language">@foreach(['nl'=>'Nederlands','fr'=>'Frans','en'=>'Engels','de'=>'Duits'] as $code=>$name)<option value="{{ $code }}" @selected(old('language',$school->settings?->language ?? 'nl')===$code)>{{ $name }}</option>@endforeach</select></label>
<label>Tijdzone<select name="timezone">@foreach(['Europe/Brussels','Europe/Amsterdam','Europe/Paris','Europe/Berlin','UTC'] as $zone)<option value="{{ $zone }}" @selected(old('timezone',$school->settings?->timezone ?? 'Europe/Brussels')===$zone)>{{ $zone }}</option>@endforeach</select></label></div>
<p>Platformbrede instellingen, SMTP en beveiligingsconfiguratie blijven uitsluitend in Platformbeheer.</p>
</div>
<div class="si-actions"><a href="{{ route('dashboard') }}" class="btn btn-secondary">Annuleren</a><button class="btn" type="submit">Schoolinstellingen opslaan</button></div>
</form>
@endsection
@push('styles')
<style>
.si-wrap{max-width:1030px}.si-card{margin-bottom:20px}.si-card h2{font-size:18px;margin:0 0 16px;padding-bottom:12px;border-bottom:1px solid #e1e8ee}.si-card p{font-size:12px;color:#718096;line-height:1.7;margin:0 0 18px}
.si-fields{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:18px}.si-fields label,.si-colors label,.si-file{display:flex;flex-direction:column;gap:8px;font-size:12px;font-weight:700}.si-wide{grid-column:1/-1}
.si-fields input,.si-fields textarea,.si-fields select,.si-color-input input[type=text],.si-file input{padding:11px 13px;border:1px solid #d1dce5;border-radius:8px;font:inherit;color:#142b45;background:#fff}.si-colors{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:15px}.si-color-input{display:flex;gap:8px}.si-color-input input[type=color]{width:52px;height:43px;padding:3px;border:1px solid #d1dce5;border-radius:8px;flex:none;cursor:pointer}.si-color-input input[type=text]{min-width:0;width:100%}
.si-preview{margin-top:24px;padding:25px 30px;border-radius:12px;background:#142b45;color:white;min-height:170px}.si-preview strong{font-size:11px;letter-spacing:1px;text-transform:uppercase;color:#59aaa5}.si-preview h3{font-size:23px;margin:15px 0 6px}.si-preview small{display:block}.si-preview-lines{display:flex;gap:8px;margin-top:18px}.si-preview-lines i{display:block;width:85px;height:5px;border-radius:8px}
.si-current{border:1px solid #dfe8ee;padding:17px;border-radius:10px;background:#f8fafb;margin-bottom:18px;display:flex;align-items:center;gap:22px;flex-wrap:wrap}.si-current img{max-width:220px;max-height:120px;object-fit:contain}.si-current label{display:flex;gap:9px;align-items:center;font-size:12px}.si-file small{font-weight:400;color:#718096}.si-actions{display:flex;gap:10px;justify-content:flex-end;margin:23px 0}.si-notice{padding:14px 17px;border-radius:9px;margin-bottom:17px}.si-success{color:#226b4c;background:#e8f6ee}.si-error{color:#a13b3b;background:#fff0f0}
@media(max-width:720px){.si-fields,.si-colors{grid-template-columns:1fr}.si-wide{grid-column:auto}}
</style>
@endpush
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded',()=>{
 const keys=['primary_color','secondary_color','accent_color'],inputs=Object.fromEntries(keys.map(k=>[k,document.getElementById(k)]));
 const valid=v=>/^#[0-9a-fA-F]{6}$/.test(v);
 function refresh(){
   if(valid(inputs.primary_color.value))document.getElementById('si-preview').style.background=inputs.primary_color.value;
   if(valid(inputs.secondary_color.value)){document.querySelector('#si-preview strong').style.color=inputs.secondary_color.value;document.getElementById('si-secondary').style.background=inputs.secondary_color.value}
   if(valid(inputs.accent_color.value))document.getElementById('si-accent').style.background=inputs.accent_color.value;
   document.getElementById('si-preview-slogan').textContent=document.querySelector('[name=slogan]').value;
   document.getElementById('si-preview-subtitle').textContent=document.querySelector('[name=subtitle]').value;
 }
 document.querySelectorAll('[data-hex]').forEach(picker=>{
   const text=inputs[picker.dataset.hex];
   picker.addEventListener('input',()=>{text.value=picker.value.toUpperCase();refresh()});
   text.addEventListener('input',()=>{if(valid(text.value))picker.value=text.value;refresh()});
 });
 document.querySelectorAll('[name=slogan],[name=subtitle]').forEach(el=>el.addEventListener('input',refresh));
 refresh();
});
</script>
@endpush
