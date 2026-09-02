@props(['label', 'column', 'sort' => '', 'dir' => 'asc'])

@php
    $active = $sort === $column;
    $nextDir = ($active && $dir === 'asc') ? 'desc' : 'asc';
    $qs = request()->except(['page']);
    $qs['sort'] = $column;
    $qs['dir'] = $nextDir;
@endphp
@php($caret = $active && $dir === 'asc' ? 'up-fill' : ($active ? 'down-fill' : 'up-fill'));
<th class="text-nowrap" style="min-width:90px">
    <a href="{{ url()->current() . '?' . http_build_query($qs) }}" class="text-decoration-none text-reset d-block">
        {{ $label }}
        <i class="bi bi-caret-{{ $caret }} small ms-1{{ $active ? '' : ' text-muted opacity-50' }}"></i>
    </a>
</th>
