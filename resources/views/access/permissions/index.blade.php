@extends('layouts.app')
@section('content')
@include('partials.flash-message')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h3>{{ ui('permissions') }}</h3>
    @can('permission.create')
    <a href="{{ route('permissions.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i> {{ ui('new_permission') }}</a>
    @endcan
</div>

<div class="d-flex align-items-center justify-content-between gap-2 flex-wrap mb-3">
    <form method="GET" class="d-flex flex-grow-1" style="max-width:420px">
        <div class="input-group input-group-sm shadow-sm w-100">
            <span class="input-group-text bg-body border-0"><i class="bi bi-search"></i></span>
            <input type="text" name="q" class="form-control bg-body border-0" placeholder="{{ ui('search_permission_name') }}" value="{{ request('q') }}" aria-label="{{ ui('search') }}">
            <button class="btn btn-primary px-3" type="submit">Search</button>
        </div>
    </form>
    @include('partials.bulk-actions', ['bulkRoute' => route('permissions.bulk'), 'canSoft' => auth()->user()->can('permission.delete'), 'canForce' => auth()->user()->can('permission.force-delete')])
</div>

<div class="card shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th style="width:38px"><input class="form-check-input" type="checkbox" form="bulk-form" id="bulk-select-all"></th>
                    <th>#</th>
                    <x-sortable-th label="{{ ui('name') }}" column="name" :sort="request('sort')" :dir="request('dir', 'asc')" />
                    <x-sortable-th label="{{ ui('guard') }}" column="guard_name" :sort="request('sort')" :dir="request('dir', 'asc')" />
                    <th class="text-end">{{ ui('action') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($permissions as $perm)
                <tr class="{{ $perm->trashed() ? 'row-deleted' : '' }}">
                    <td><input class="form-check-input" type="checkbox" form="bulk-form" name="ids[]" value="{{ $perm->id }}"></td>
                    <td class="text-muted">{{ $permissions->firstItem() + $loop->index }}</td>
                    <td>{{ $perm->name }}</td>
                    <td>
                        <span class="badge text-bg-secondary">{{ $perm->guard_name }}</span>
                                @if($perm->trashed())<span class="badge text-bg-danger">{{ ui('deleted') }}</span>@endif
                    </td>
                    <td class="text-end">
                        <x-action-buttons
                            :edit="$perm->trashed() ? null : route('permissions.edit', $perm)"
                            :restore="$perm->trashed() && auth()->user()->can('permission.restore') ? route('permissions.restore', $perm->id) : null"
                            :delete="$perm->trashed() ? null : route('permissions.destroy', $perm)"
                            :forceDelete="$perm->trashed() && auth()->user()->can('permission.force-delete') ? route('permissions.forceDelete', $perm->id) : null" />
                    </td>
                </tr>
                @empty
                <tr><td colspan="4" class="text-center text-muted py-4">{{ ui('no_permissions') }}</td></tr>
                @endforelse
            </tbody>
        </table>
        </div>
    </div>
</div>
@include('partials.pagination-info', ['items' => $permissions])
{{ $permissions->links() }}
@include('partials.modals.delete-modal')
@include('partials.modals.force-delete-modal')
@endsection
