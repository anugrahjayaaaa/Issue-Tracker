@props(['label', 'column', 'sort' => '', 'dir' => 'asc'])

@php
    $active = $sort === $column;
    $nextDir = ($active && $dir === 'asc') ? 'desc' : 'asc';
    $qs = request()->except(['page']);
    $qs['sort'] = $column;
    $qs['dir'] = $nextDir;
@endphp
<th>
    <a href="{{ url()->current() . '?' . http_build_query($qs) }}" class="text-decoration-none text-reset text-nowrap">
        {{ $label }}
        @if ($active)
            <i class="bi bi-caret-{{ $dir === 'asc' ? 'up' : 'down' }}-fill small ms-1"></i>
        @else
            <i class="bi bi-caret-up small text-muted opacity-50 ms-1"></i>
        @endif
    </a>
</th>
