@extends('layouts.app')

@section('title', 'Edit Translation')

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <h1 class="m-0 h3">{{ __('messages.translations') }}: {{ $line->group }}.{{ $line->key }}</h1>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <div class="card">
            <div class="card-body">
                <form method="POST" action="{{ route('translations.update', $line) }}">
                    @csrf
                    @method('PUT')
                    @foreach($locales as $locale)
                        <div class="mb-3">
                            <label class="form-label text-uppercase">{{ $locale }}</label>
                            <input type="text" name="{{ $locale }}" class="form-control"
                                   value="{{ old($locale, $line->text[$locale] ?? '') }}" required>
                        </div>
                    @endforeach
                    <button type="submit" class="btn btn-primary">{{ __('messages.save') }}</button>
                    <a href="{{ route('translations.index') }}" class="btn btn-link">{{ ui('cancel') }}</a>
                </form>
            </div>
        </div>
    </div>
</section>
@endsection
