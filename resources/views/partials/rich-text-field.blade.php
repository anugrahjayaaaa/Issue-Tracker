{{-- Rich-text editor (TinyMCE 7, self-hosted) for a single HTML field.
     Mirrors the labels-field partial pattern. OSS (MPL/GPL/LGPL), no license key.
     ponytail: toolbar is the full default set; trim to a profile if you want less. --}}
@php
    $name = $name ?? 'description';
    $id = $id ?? $name.'-editor';
    $inputId = $name.'_input';
    $value = $value ?? old($name);
    $uploadUrl = $uploadUrl ?? null; // route accepting multipart `file`, returns {location}
    $uploadUrl = $uploadUrl ?: '';   // empty = no upload (e.g. create form, project not saved yet)
@endphp

<div class="mb-3">
    <label class="form-label">{{ $label ?? ui('description') }}</label>
    <textarea name="{{ $name }}" id="{{ $inputId }}" class="@error($name) is-invalid @enderror">{{ $value }}</textarea>
    @error($name)<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
</div>

@push('scripts')
<script src="{{ asset('vendor/tinymce/tinymce.min.js') }}" referrerpolicy="origin"></script>
<script>
(function () {
    if (typeof tinymce === 'undefined') return;
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
    const init = {selector: '#{{ $inputId }}',
        base_url: '{{ asset('vendor/tinymce') }}',
        skin: 'oxide-dark',
        content_css: 'dark',
        content_style: 'body{background:#171a21;color:#e5e7eb;font-family:Inter,system-ui,sans-serif;}',
        menubar: false,
        plugins: 'lists link image table code autolink charmap preview searchreplace',
        toolbar: 'undo redo | blocks | bold italic underline strikethrough | ' +
            'alignleft aligncenter alignright | bullist numlist outdent indent | ' +
            'link image table blockquote code | removeformat',
        branding: false,
        height: 320,
        // ponytail: model sanitizeRichText() keeps only local /storage/projects/ images on save.
        valid_elements: '*[*]',
        entity_encoding: 'raw',
        images_upload_handler: function (blobInfo, progress) {
            @if ($uploadUrl)
            const fd = new FormData();
            fd.append('file', blobInfo.blob(), blobInfo.filename());
            return fetch('{{ $uploadUrl }}', {method: 'POST', headers: { 'X-CSRF-TOKEN': csrf }, body: fd})
                .then(r => r.json())
                .then(d => d.location)
                .catch(() => Promise.reject('upload failed'));
            @else
            return Promise.reject('no upload url');
            @endif
        },
    };
    tinymce.init(init);
})();
</script>
@endpush
