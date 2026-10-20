{{-- Template download for an import form; $template = file name in public/templates, or pass $url for a generated template. Place it above the file input. --}}
<div style="margin: 4px 0 12px;">
    <a href="{{ $url ?? asset('templates/'.$template) }}" download class="btn btn-default border-primary btn-rounded" style="font-size:15px; padding:8px 18px;">
        <i class="fa fa-download"></i> Download Template
    </a>
    <div class="text-muted" style="font-size:13px; margin-top:6px;">
        The example row starts with # and is skipped on upload, so you can leave it in.
    </div>
</div>
