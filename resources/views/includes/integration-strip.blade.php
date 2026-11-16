{{--
    "Integrates with" strip for the login screens.
    Drop a logo at public/images/integrations/{slug}.svg (or .png) and it replaces
    the text wordmark for that integration automatically.
--}}
@php
    $integrationStrip = [
        'sage-intacct'   => 'Sage Intacct',
        'sage-pastel'    => 'Sage Pastel',
        'cartrack'       => 'Cartrack',
        'ezytrack'       => 'EzyTrack',
        'pinpoint'       => 'Pinpoint',
        'fantracker'     => 'FanTracker',
        'fiscal-harmony' => 'Fiscal Harmony',
        'revmax'         => 'Revmax',
    ];
@endphp

<style>
    .integration-strip { margin-top: 15px; text-align: center; }
    .integration-strip__caption {
        display: block;
        margin-bottom: 8px;
        font-size: 10px;
        letter-spacing: 1.5px;
        text-transform: uppercase;
        color: #9aa0a6;
    }
    .integration-strip__list {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: center;
        gap: 6px 16px;
        margin: 0;
        padding: 0;
        list-style: none;
    }
    .integration-strip__item {
        font-size: 12px;
        font-weight: 600;
        line-height: 20px;
        white-space: nowrap;
        color: #7b8187;
    }
    .integration-strip__item img {
        display: block;
        height: 20px;
        width: auto;
        max-width: 90px;
        -webkit-filter: grayscale(100%);
        filter: grayscale(100%);
        opacity: .65;
    }
</style>

<div class="integration-strip">
    <span class="integration-strip__caption">Integrates with</span>
    <ul class="integration-strip__list">
        @foreach ($integrationStrip as $slug => $name)
            @php
                $logo = null;
                foreach (['svg', 'png'] as $ext) {
                    if (file_exists(public_path("images/integrations/{$slug}.{$ext}"))) {
                        $logo = asset("images/integrations/{$slug}.{$ext}");
                        break;
                    }
                }
            @endphp
            <li class="integration-strip__item">
                @if ($logo)
                    <img src="{{ $logo }}" alt="{{ $name }}" title="{{ $name }}">
                @else
                    {{ $name }}
                @endif
            </li>
        @endforeach
    </ul>
</div>
