@extends('layouts.platform')
@section('title','Schoolgebruikers')
@section('page-title','Schoolgebruikers')
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
        <div><h2>Gebruikers van deze school</h2><p>{{ $members->total() }} koppelingen</p></div>
        <a class="btn" href="{{ route('platform.users.create') }}">+ Gebruiker toevoegen</a>
    </div>
    <div style="overflow-x:auto">
        <table class="su-table">
            <thead><tr><th>Naam</th><th>E-mailadres</th><th>Rol in deze school</th><th>Status</th><th>Acties</th></tr></thead>
            <tbody>
            @forelse($members as $member)
                <tr>
                    <td><strong>{{ $member->full_name ?: $member->name }}</strong></td>
                    <td>{{ $member->email }}</td>
                    <td>{{ $roleNames->get($member->pivot->role, $member->pivot->role) }}</td>
                    <td><span class="su-pill {{ $member->pivot->is_active ? 'on' : 'off' }}">{{ $member->pivot->is_active ? 'Actief' : 'Inactief' }}</span></td>
                    <td><a class="btn btn-secondary" href="{{ route('platform.users.edit',$member) }}">Bewerken</a></td>
                </tr>
            @empty
                <tr><td colspan="5">Er zijn nog geen gebruikers aan deze school gekoppeld.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div style="margin-top:22px">{{ $members->links() }}</div>
</div>
@endsection
@push('styles')
<style>
.su-head{display:flex;align-items:center;justify-content:space-between;gap:16px;flex-wrap:wrap}.su-head h2{margin:0 0 6px;font-size:19px}.su-head p{margin:0 0 18px;color:#718096;font-size:12px}.su-table{width:100%;border-collapse:collapse}.su-table th,.su-table td{text-align:left;padding:15px 12px;border-bottom:1px solid #edf0f3;font-size:12px}.su-table th{font-size:11px;color:#718096;text-transform:uppercase;white-space:nowrap}.su-pill{display:inline-block;padding:7px 11px;border-radius:20px;font-weight:700;font-size:10px}.su-pill.on{background:#e8f7ee;color:#25845b}.su-pill.off{background:#fceeee;color:#b94747}
</style>
@endpush
