@extends('layouts.app')

@section('title', 'Translations')

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <h1 class="m-0 h3">{{ __('messages.translations') }}</h1>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        @if(session('status'))
            <div class="alert alert-success">{{ session('status') }}</div>
        @endif
        <div class="card">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>{{ ui('group') }}</th>
                                <th>{{ ui('key') }}</th>
                                <th>EN</th>
                                <th>ID</th>
                                <th class="text-end">{{ ui('action') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($lines as $line)
                                <tr>
                                    <td><span class="badge text-bg-secondary">{{ $line->group }}</span></td>
                                    <td><code>{{ $line->key }}</code></td>
                                    <td>{{ $line->text['en'] ?? '' }}</td>
                                    <td>{{ $line->text['id'] ?? '' }}</td>
                                    <td class="text-end">
                                        <a href="{{ route('translations.edit', $line) }}" class="btn btn-sm btn-outline-primary">{{ ui('edit') }}</a>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-center text-muted py-4">{{ ui('no_translations') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($lines->hasPages())
            <div class="card-footer">
                {{ $lines->links() }}
            </div>
            @endif
        </div>
    </div>
</section>
@endsection
