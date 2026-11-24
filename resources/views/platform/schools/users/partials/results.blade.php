<div class="su-results-heading">
    <strong>{{ number_format($members->total(), 0, ',', '.') }}</strong>
    <span>{{ $members->total() === 1 ? 'schoolgebruiker' : 'schoolgebruikers' }} gevonden</span>
</div>
<div style="overflow-x:auto">
    <table class="su-table">
        <thead>
        <tr>
            <th>Naam</th><th>E-mailadres</th><th>Rol in deze school</th><th>Status schoolkoppeling</th><th>Acties</th>
        </tr>
        </thead>
        <tbody>
        @forelse($members as $member)
            <tr>
                <td><strong>{{ $member->full_name ?: $member->name }}</strong></td>
                <td>{{ $member->email }}</td>
                <td>{{ $roleNames->get($member->pivot->role, $member->pivot->role) }}</td>
                <td>
                    <span class="su-pill {{ $member->pivot->is_active ? 'on' : 'off' }}">
                        {{ $member->pivot->is_active ? 'Actief' : 'Inactief' }}
                    </span>
                </td>
                <td><a class="btn btn-secondary" href="{{ route('platform.users.edit', ['user' => $member, 'school_id' => $school->id]) }}">Bewerken</a></td>
            </tr>
        @empty
            <tr><td colspan="5" style="text-align:center;padding:30px;color:#718096">Geen gebruikers gevonden. Pas je filters aan.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
<div style="margin-top:22px">{{ $members->links('vendor.pagination.artelynx') }}</div>
