{{--
    THE layout for every email the system sends - templates only provide
    @section('title'), optional @section('heading') and @section('content').
    Header band (company colour + logo + name + heading), white card, footer.
    Email-client safe: tables + inline styles only, no external CSS/JS.

    Shared bits for content: use the classes below via inline styles, e.g.
      <p style="margin:0 0 14px 0;font-size:15px;line-height:23px;">…</p>
      button: @include('emails.partials.button', ['url' => …, 'label' => …])
      detail table: @include('emails.partials.details', ['rows' => ['Label' => 'value', …]])
--}}
@php
    $mailCompany = $company ?? null;
    $brand = ($mailCompany && !empty($mailCompany->color)) ? $mailCompany->color : '#123bdc';
    $brandName = $mailCompany->name ?? config('app.name', 'Gonyeti TLS');

    // Logo: embedded inline (cid:) when sending, so it never depends on the
    // server URL; plain asset URL when previewing in a browser.
    $logoSrc = null;
    if ($mailCompany && !empty($mailCompany->logo)) {
        $logoPath = public_path('images/uploads/' . $mailCompany->logo);
        if (is_file($logoPath)) {
            $logoSrc = isset($message) ? $message->embed($logoPath) : asset('images/uploads/' . $mailCompany->logo);
        }
    }
    $heading = trim($__env->yieldContent('heading'));
@endphp
<!DOCTYPE html>
<html lang="en" xmlns="http://www.w3.org/1999/xhtml" xmlns:o="urn:schemas-microsoft-com:office:office">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="x-apple-disable-message-reformatting">
    <title>@yield('title')</title>
    <style>
        table, td, div, h1, h2, h3, p, a { font-family: Arial, Helvetica, sans-serif; }
        @media screen and (max-width: 620px) {
            .mail-card { width: 100% !important; }
            .mail-pad { padding-left: 18px !important; padding-right: 18px !important; }
        }
    </style>
</head>
<body style="margin:0;padding:0;background:#f2f4f7;">
<table role="presentation" style="width:100%;border-collapse:collapse;border:0;border-spacing:0;background:#f2f4f7;">
    <tr>
        <td align="center" style="padding:24px 10px;">
            <table role="presentation" class="mail-card" style="width:602px;max-width:100%;border-collapse:collapse;border:1px solid #d9dde3;border-spacing:0;text-align:left;background:#ffffff;">

                {{-- Header --}}
                <tr>
                    <td align="center" style="padding:26px 30px 22px 30px;background:{{ $brand }};">
                        @if($logoSrc)
                            <img src="{{ $logoSrc }}" alt="{{ $brandName }}" style="max-height:60px;max-width:220px;height:auto;display:block;margin:0 auto 10px auto;background:#ffffff;padding:6px;border-radius:4px;" />
                        @endif
                        <div style="color:#ffffff;font-size:14px;letter-spacing:.04em;text-transform:uppercase;opacity:.9;">{{ $brandName }}</div>
                        @if($heading !== '')
                            <h1 style="margin:8px 0 0 0;color:#ffffff;font-size:22px;line-height:28px;font-weight:bold;">{!! $heading !!}</h1>
                        @endif
                    </td>
                </tr>

                {{-- Body --}}
                <tr>
                    <td class="mail-pad" style="padding:30px 30px 34px 30px;color:#153643;font-size:15px;line-height:23px;">
                        @yield('content')
                    </td>
                </tr>

                {{-- Footer --}}
                <tr>
                    <td style="padding:18px 30px;background:{{ $brand }};">
                        <table role="presentation" style="width:100%;border-collapse:collapse;border:0;border-spacing:0;">
                            <tr>
                                <td style="padding:0;color:#ffffff;font-size:12px;line-height:18px;">
                                    {{ $brandName }}@if($mailCompany && !empty($mailCompany->website)) · <a href="{{ $mailCompany->website }}" style="color:#ffffff;text-decoration:underline;">{{ preg_replace('#^https?://#', '', rtrim($mailCompany->website, '/')) }}</a>@endif
                                    <br>
                                    Powered by Gonyeti TLS &reg; {{ date('Y') }} · <a href="mailto:info@basilmark.com" style="color:#ffffff;text-decoration:underline;">info@basilmark.com</a>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
            <div style="max-width:602px;margin:10px auto 0 auto;color:#8a929c;font-size:11px;line-height:16px;text-align:center;">
                This is an automated message from {{ $brandName }}.
            </div>
        </td>
    </tr>
</table>
</body>
</html>
