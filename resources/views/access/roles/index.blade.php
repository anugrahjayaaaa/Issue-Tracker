@extends('layouts.app')
@section('content')
@include('partials.flash-message')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h3>{{ ui('roles') }}</h3>
    @can('role.create')
    <a href="{{ route('roles.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i> {{ ui('new_role') }}</a>
    @endcan
</div>

<div class="d-flex align-items-center justify-content-between gap-2 flex-wrap mb-3">
    <form method="GET" class="d-flex flex-grow-1" style="max-width:420px">
        <div class="input-group input-group-sm shadow-sm w-100">
            <span class="input-group-text bg-body border-0"><i class="bi bi-search"></i></span>
            <input type="text" name="q" class="form-control bg-body border-0" placeholder="{{ ui('search_role_name') }}" value="{{ request('q') }}" aria-label="{{ ui('search') }}">
            <button class="btn btn-primary px-3" type="submit">Search</button>
        </div>
    </form>
    @include('partials.bulk-actions', ['bulkRoute' => route('roles.bulk'), 'canSoft' => auth()->user()->can('role.delete'), 'canForce' => auth()->user()->can('role.force-delete')])
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
                    <th>{{ ui('permissions') }}</th><th class="text-end">{{ ui('action') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($roles as $role)
                <tr class="{{ $role->trashed() ? 'row-deleted' : '' }}">
                    <td><input class="form-check-input" type="checkbox" form="bulk-form" name="ids[]" value="{{ $role->id }}"></td>
                    <td class="text-muted">{{ $roles->firstItem() + $loop->index }}</td>
                    <td>
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle">{{ $role->name }}</span>
                        @if($role->trashed())<span class="badge text-bg-danger">{{ ui('deleted') }}</span>@endif
                    </td>
                    <td><span class="badge bg-secondary-subtle text-secondary">{{ $role->permissions->count() }}</span></td>
                    <td class="text-end">
                        <x-action-buttons
                            :edit="$role->trashed() ? null : route('roles.edit', $role)"
                            :restore="$role->trashed() && auth()->user()->can('role.restore') ? route('roles.restore', $role->id) : null"
                            :delete="!$role->trashed() && $role->name !== 'super-admin' ? route('roles.destroy', $role) : null"
                            :forceDelete="$role->trashed() && auth()->user()->can('role.force-delete') ? route('roles.forceDelete', $role->id) : null" />
                    </td>
                </tr>
                @empty
                <tr><td colspan="4" class="text-center text-muted py-4">{{ ui('no_roles') }}</td></tr>
                @endforelse
            </tbody>
        </table>
        </div>
    </div>
</div>
@include('partials.pagination-info', ['items' => $roles])
{{ $roles->links() }}
@include('partials.modals.delete-modal')
@include('partials.modals.force-delete-modal')
@endsection
