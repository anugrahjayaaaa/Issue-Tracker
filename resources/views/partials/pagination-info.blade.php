@if (isset($items) && method_exists($items, 'firstItem'))
<div class="text-muted small mb-2">
    Showing {{ $items->firstItem() }}–{{ $items->lastItem() }} {{ ui('of') }} {{ $items->total() }}
</div>
@endif
