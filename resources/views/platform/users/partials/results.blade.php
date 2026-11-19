<div class="um-results-heading">
    <strong>{{ number_format($users->total(), 0, ',', '.') }} {{ $users->total() === 1 ? 'personeelslid' : 'personeelsleden' }}</strong>
    <span>gevonden</span>
</div>
<div class="um-table-scroll">
    <table class="um-table">
        <thead>
            <tr>
                <th><input type="checkbox" id="um-select-all" aria-label="Selecteer alle personeelsleden op deze pagina"></th>
                <th>Personeelslid</th>
                <th>School / rol</th>
                <th>Platform</th>
                <th>Account</th>
                <th>Wachtwoord</th>
                <th>Acties</th>
            </tr>
        </thead>
        <tbody>
            @forelse($users as $user)
                <tr>
                    <td><input type="checkbox" form="um-bulk-form" class="um-person-check" name="user_ids[]" value="{{ $user->id }}" aria-label="Selecteer {{ $user->full_name ?: $user->name }}"></td>
                    <td>
                        <strong>{{ $user->full_name ?: $user->name }}</strong>
                        <small>{{ $user->email }}</small>
                    </td>
                    <td>
                        @forelse($user->schools as $school)
                            @php
                                $displayRole = $roles->first(
                                    fn ($r) => (int) $r->school_id === (int) $school->id && $r->slug === $school->pivot->role
                                );
                            @endphp
                            <div class="um-membership">
                                <strong>{{ $school->name }}</strong>
                                <span>{{ $displayRole?->name ?? $school->pivot->role }}</span>
                                @unless($school->pivot->is_active)
                                    <em>Schoolkoppeling inactief</em>
                                @endunless
                            </div>
                        @empty
                            <span class="um-muted">Geen schoolkoppelingen</span>
                        @endforelse
                    </td>
                    <td>
                        @if($user->is_platform_admin)
                            <span class="um-tag">Platformadmin</span>
                        @else
                            <span class="um-muted">—</span>
                        @endif
                    </td>
                    <td>
                        <span class="um-tag {{ $user->is_active ? 'um-on' : 'um-off' }}">
                            {{ $user->is_active ? 'Actief' : 'Inactief' }}
                        </span>
                    </td>
                    <td>
                        @if($user->must_change_password)
                            <span class="um-tag um-off">Nog instellen</span>
                        @elseif($user->password_set_at)
                            <span class="um-tag um-on" title="Ingesteld op {{ $user->password_set_at->format('d/m/Y') }}">Ingesteld</span>
                        @else
                            <span class="um-tag" title="Account bestaat van vóór de wachtwoordregistratie">Onbekend (oud account)</span>
                        @endif
                    </td>
                    <td>
                        <div class="um-actions">
                            <a class="btn btn-secondary" href="{{ route('platform.users.edit', $user) }}">Bewerken</a>
                            <form method="POST" action="{{ route('platform.users.setup-link', $user) }}"
                                  onsubmit="return confirm('Een beveiligde instellink versturen?');">
                                @csrf
                                <button class="btn btn-secondary" type="submit">Instellink</button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="um-empty">Geen gebruikers gevonden. Pas je zoekterm of filters aan.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="um-pagination">{{ $users->links() }}</div>
