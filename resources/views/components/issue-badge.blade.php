@props(['issue', 'field' => 'status'])

@php
    // ponytail: single source for status/type badges — resolves color + label
    // from the issue's project field by stable key. No duplicate inline styles.
    if ($field === 'type') {
        $label = $issue->typeName();
        $color = $issue->typeColor();
    } else {
        $label = $issue->statusName();
        $color = $issue->statusColor();
    }
    $color = $color ?? '#6c757d';
@endphp

<span class="badge" style="background:{{ $color }};color:#fff">{{ $label }}</span>
