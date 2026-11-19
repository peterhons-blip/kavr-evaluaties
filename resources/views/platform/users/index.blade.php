@extends('layouts.platform')
@section('title', 'Gebruikers')
@section('page-title', 'Gebruikersbeheer')
@section('content')
<div class="page-header">
    <h1>Gebruikers</h1>
    <p>Beheer accounts centraal. Elke gebruiker kan afzonderlijke rollen hebben per school.</p>
</div>
@if(session('success')) <div class="um-notice">{{ session('success') }}</div> @endif
@if($errors->any())<div class="um-notice" style="background:#fff1f2;color:#9f1239">@foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach</div>@endif
<div class="card">
    <div class="um-heading">
        <div><h2>Alle gebruikers</h2><p>{{ $users->total() }} accounts</p></div>
        <a class="btn" href="{{ route('platform.users.create') }}">+ Gebruiker toevoegen</a>
    </div>
    <form method="GET" class="um-search">
        <input type="search" name="q" value="{{ $search }}" placeholder="Zoek op naam of e-mailadres" aria-label="Gebruikers zoeken">
        <button type="submit" class="btn btn-secondary">Zoeken</button>
    </form>
    <div style="overflow-x:auto">
        <table class="um-table">
            <thead><tr><th>Gebruiker</th><th>Scholen / rollen</th><th>Platform</th><th>Status</th><th>Acties</th></tr></thead>
            <tbody>
                @forelse($users as $user)
                <tr>
                    <td><strong>{{ $user->full_name ?: $user->name }}</strong><small>{{ $user->email }}</small></td>
                    <td>
                        @forelse($user->schools as $school)
                            <div class="um-membership">{{ $school->name }} <span>({{ $school->pivot->role }})</span>
                            @unless($school->pivot->is_active) <em>— inactief</em> @endunless</div>
                        @empty <span class="um-muted">Geen schoolkoppelingen</span> @endforelse
                    </td>
                    <td>@if($user->is_platform_admin)<span class="um-tag">Platformadmin</span>@else — @endif</td>
                    <td><span class="um-tag {{ $user->is_active ? 'um-on' : 'um-off' }}">{{ $user->is_active ? 'Actief' : 'Inactief' }}</span></td>
                    <td><div style="display:flex;flex-wrap:wrap;gap:7px;">
    <a class="btn btn-secondary" href="{{ route('platform.users.edit',$user) }}">Bewerken</a>
    <form method="POST" action="{{ route('platform.users.setup-link',$user) }}" onsubmit="return confirm('Beveiligde instellink versturen?');">
        @csrf <button class="btn btn-secondary" type="submit">Instellink</button>
    </form>
</div></td>
                </tr>
                @empty <tr><td colspan="5">Geen gebruikers gevonden.</td></tr> @endforelse
            </tbody>
        </table>
    </div>
    <div style="margin-top:20px">{{ $users->links() }}</div>
</div>
@endsection
@push('styles')
<style>
.um-heading{display:flex;justify-content:space-between;align-items:center;gap:16px;flex-wrap:wrap}.um-heading h2{margin:0 0 5px}.um-heading p{margin:0 0 20px;color:#718096}.um-search{display:flex;gap:10px;margin-bottom:20px}.um-search input{flex:1;min-width:0;padding:12px;border:1px solid #d5dfe7;border-radius:8px}.um-table{width:100%;border-collapse:collapse}.um-table th,.um-table td{padding:15px;text-align:left;border-bottom:1px solid #e8eef2;vertical-align:middle}.um-table th{font-size:11px;color:#718096;text-transform:uppercase}.um-table td{font-size:12px}.um-table td small{display:block;color:#718096;margin-top:6px}.um-membership{margin:5px 0}.um-membership span,.um-muted{color:#718096}.um-membership em{color:#b94747}.um-tag{display:inline-flex;padding:6px 10px;border-radius:20px;background:#eef2f7;color:#526278;font-weight:700;font-size:10px}.um-on{background:#e9f7ef;color:#247958}.um-off{background:#fff0f0;color:#b94747}.um-notice{padding:15px;background:#edf8f3;border-radius:9px;color:#246b52;margin-bottom:20px}
</style>
@endpush
