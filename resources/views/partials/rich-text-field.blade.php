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
    const selector = '#{{ $inputId }}';
    const uploadUrl = @json($uploadUrl);

    const isLight = () => document.documentElement.getAttribute('data-bs-theme') === 'light';

    function initEditor() {
        const light = isLight();
        tinymce.init({
            selector,
            base_url: '{{ asset('vendor/tinymce') }}',
            skin: light ? 'oxide' : 'oxide-dark',
            content_css: light ? 'default' : 'dark',
            content_style: light
                ? 'body{background:#ffffff;color:#111827;font-family:Inter,system-ui,sans-serif;}'
                : 'body{background:#171a21;color:#e5e7eb;font-family:Inter,system-ui,sans-serif;}',
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
            images_upload_handler: function (blobInfo) {
                if (!uploadUrl) return Promise.reject('no upload url');
                const fd = new FormData();
                fd.append('file', blobInfo.blob(), blobInfo.filename());
                return fetch(uploadUrl, {method: 'POST', headers: {'X-CSRF-TOKEN': csrf}, body: fd})
                    .then(r => r.json())
                    .then(d => d.location)
                    .catch(() => Promise.reject('upload failed'));
            },
        });
    }

    initEditor();

    // Follow the app's theme live: TinyMCE 7 has no runtime setSkin/setTheme,
    // so re-init with the correct skin. Content is flushed to the <textarea>
    // on destroy, so re-init restores it. ponytail: single global observer —
    // covers the layout toggle and any future switch, no per-button wiring.
    let theme = isLight() ? 'light' : 'dark';
    new MutationObserver(() => {
        const next = isLight() ? 'light' : 'dark';
        if (next === theme) return;
        theme = next;
        const inst = tinymce.get(selector.replace('#', ''));
        if (inst) inst.remove();
        initEditor();
    }).observe(document.documentElement, {attributes: true, attributeFilter: ['data-bs-theme']});
})();
</script>
@endpush
