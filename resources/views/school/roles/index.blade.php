@extends('layouts.school')
@section('title','Rollen en rechten')
@section('page-title','Rollen en rechten')
@section('content')
<div class="page-header"><h1>Rollen en rechten</h1><p>Overzicht van alle bevoegdheden in {{ $school->name }}. De rechten van andere academies blijven afzonderlijk.</p></div>
@if(session('success'))<div class="sr-notice sr-success">{{ session('success') }}</div>@endif
@if($errors->any())<div class="sr-notice sr-error">@foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach</div>@endif
<div class="card sr-card">
  <div class="sr-header"><div><h2>Rechtenmatrix</h2><p>De vinkjes tonen de rechten per rol. Klik op een rolnaam om de bevoegdheden te bewerken.</p></div><a class="btn" href="{{ route('school.roles.create') }}">+ Nieuwe schoolrol</a></div>
  <div class="sr-scroll"><table class="sr-matrix"><thead><tr><th>Categorie / onderdeel / bevoegdheid</th>
  @foreach($roles as $role)<th><a class="sr-role-link" href="{{ route('school.roles.edit',$role) }}">{{ $role->name }} <span aria-hidden="true">✎</span></a></th>@endforeach
  </tr></thead><tbody>
  @foreach(\App\Support\SchoolRoleCatalog::CATEGORIES as $category=>$modules)
     <tr class="sr-category"><th colspan="{{ $roles->count()+1 }}">{{ $category }}</th></tr>
     @foreach($modules as $module=>$permissions)
       <tr class="sr-module"><th colspan="{{ $roles->count()+1 }}">{{ $module }}</th></tr>
       @foreach($permissions as $key)
         <tr><td>{{ \App\Support\SchoolRoleCatalog::PERMISSIONS[$key] }}<small>{{ $key }}</small></td>
         @foreach($roles as $role)<td class="sr-check">@if(in_array($key,$role->permissions ?? [],true))<span class="sr-yes" title="Toegestaan">✓</span>@else<span class="sr-no" title="Niet toegestaan">—</span>@endif</td>@endforeach
         </tr>
       @endforeach
     @endforeach
  @endforeach
  </tbody></table></div>
</div>
<div class="card sr-card">
  <div class="sr-header"><div><h2>Rollen beheren</h2><p>Standaardrollen kun je aanpassen. Eigen rollen kun je verwijderen zolang ze niet aan medewerkers zijn toegekend.</p></div></div>
  <div class="sr-list">
  @foreach($roles as $role)
     <div class="sr-row"><div class="sr-row-copy"><strong>{{ $role->name }}</strong><p>{{ $role->description ?: 'Geen beschrijving' }}</p><small>{{ count($role->permissions ?? []) }} rechten · {{ $role->is_system ? 'Standaardrol' : 'Eigen rol' }}</small></div>
     <div class="sr-actions"><a class="btn btn-secondary" href="{{ route('school.roles.edit',$role) }}">Bewerken</a>
     @unless($role->is_system)<form method="POST" action="{{ route('school.roles.destroy',$role) }}" onsubmit="return confirm('Deze rol verwijderen?')">@csrf @method('DELETE')<button class="sr-danger" type="submit">Verwijderen</button></form>@endunless
     </div></div>
  @endforeach
  </div>
</div>
@endsection
@push('styles')
<style>
.sr-card{margin-bottom:22px}.sr-header{display:flex;align-items:start;justify-content:space-between;gap:14px;flex-wrap:wrap;margin-bottom:22px}
.sr-header h2{font-size:18px;margin:0 0 7px}.sr-header p{font-size:12px;color:#718096;margin:0;line-height:1.7}
.sr-scroll{overflow-x:auto;border:1px solid #e1e8ee;border-radius:10px}.sr-matrix{border-collapse:separate;border-spacing:0;width:100%;min-width:850px;font-size:12px}
.sr-matrix th,.sr-matrix td{border-bottom:1px solid #eaf0f4;padding:12px;text-align:left}.sr-matrix thead th{background:#f7f9fb;text-align:center;min-width:135px}.sr-matrix thead th:first-child{text-align:left;min-width:265px}
.sr-matrix td small{display:block;color:#94a3b8;font-size:10px;margin-top:3px}
.sr-role-link{display:inline-flex;gap:7px;align-items:center;background:#e9f3f2;color:#286e69;padding:9px 11px;text-decoration:none;border-radius:8px;font-weight:700;border:1px solid #cbdedc;white-space:nowrap}
.sr-role-link:hover{background:#d7ebe8}.sr-matrix .sr-category th{background:#eaf1f5;font-weight:800;text-align:left}.sr-matrix .sr-module th{background:#f7fafb;text-align:left;color:#49687a}
.sr-matrix td.sr-check{text-align:center}.sr-yes{display:inline-grid;place-items:center;background:#e3f3ed;color:#207653;font-size:15px;font-weight:800;width:25px;height:25px;border-radius:50%}.sr-no{color:#a5b1bc;font-size:17px}
.sr-row{padding:17px 0;display:flex;align-items:center;justify-content:space-between;gap:15px;border-bottom:1px solid #eaf0f4}.sr-row:last-child{border:0}.sr-row-copy strong{font-size:13px}.sr-row-copy p{font-size:12px;color:#718096;margin:4px 0}.sr-row-copy small{color:#718096;font-size:11px}.sr-actions{display:flex;align-items:center;gap:9px;flex-wrap:wrap}.sr-danger{background:#fff1f2;color:#b94747;border:1px solid #fecdd3;padding:10px 13px;border-radius:8px;cursor:pointer;font:inherit;font-size:12px;font-weight:700}.sr-danger:hover{background:#fee2e2}.sr-notice{padding:14px 17px;border-radius:9px;margin-bottom:17px}.sr-success{background:#eaf7ef;color:#267450}.sr-error{background:#fff0f0;color:#a64949}
@media(max-width:700px){.sr-row{align-items:start;flex-direction:column}}
</style>
@endpush
