@if ($paginator->hasPages())
    <nav aria-label="Paginering" class="ax-pagination" style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;margin-top:20px;font-size:13px">
        <div style="color:#64748b">
            Resultaten <strong>{{ $paginator->firstItem() }}</strong> t/m <strong>{{ $paginator->lastItem() }}</strong>
            van <strong>{{ $paginator->total() }}</strong>
        </div>
        <div style="display:flex;align-items:center;flex-wrap:wrap;gap:6px">
            @if ($paginator->onFirstPage())
                <span aria-disabled="true" style="padding:8px 12px;color:#94a3b8;border:1px solid #e2e8f0;border-radius:8px">Vorige</span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev" style="padding:8px 12px;border:1px solid #d5dfe7;border-radius:8px;color:#142b45;text-decoration:none;background:#fff">Vorige</a>
            @endif
            @foreach ($elements as $element)
                @if (is_string($element))
                    <span aria-hidden="true" style="padding:8px 4px;color:#64748b">{{ $element }}</span>
                @elseif (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span aria-current="page" style="padding:8px 12px;border-radius:8px;background:#142b45;color:#fff;font-weight:700">{{ $page }}</span>
                        @else
                            <a href="{{ $url }}" aria-label="Ga naar pagina {{ $page }}" style="padding:8px 12px;border:1px solid #d5dfe7;border-radius:8px;color:#142b45;text-decoration:none;background:#fff">{{ $page }}</a>
                        @endif
                    @endforeach
                @endif
            @endforeach
            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next" style="padding:8px 12px;border:1px solid #d5dfe7;border-radius:8px;color:#142b45;text-decoration:none;background:#fff">Volgende</a>
            @else
                <span aria-disabled="true" style="padding:8px 12px;color:#94a3b8;border:1px solid #e2e8f0;border-radius:8px">Volgende</span>
            @endif
        </div>
    </nav>
@endif
