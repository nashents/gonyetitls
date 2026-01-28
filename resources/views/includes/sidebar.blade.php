{{-- resources/views/includes/sidebar.blade.php --}}

@if($companyColor)
    <style>
        .bg-black-300 { background-color: {{ $companyColor }}; }
    </style>
@endif

@php
    use App\Support\Menu;

    // Decode JSON that may come as string from DB
    $json = function ($val) {
        if ($val === null) return null;
        if (is_array($val)) return $val;
        if (is_string($val)) {
            $decoded = json_decode($val, true);
            return json_last_error() === JSON_ERROR_NONE ? $decoded : null;
        }
        return null;
    };

    // Inherit visibility: child(null) -> parent visibility
    $inheritVis = function ($childVis, $parentVis) use ($json) {
        $child = $json($childVis);
        return $child !== null ? $child : $json($parentVis);
    };

    // Route params can also be JSON string
    $routeParams = function ($val) use ($json) {
        $decoded = $json($val);
        return is_array($decoded) ? $decoded : [];
    };
@endphp

    <style>
    /* --- Module Group collapse styles --- */
    .nav-group-toggle{
        display:flex;
        align-items:center;
        justify-content:space-between;
        padding:10px 12px;
        cursor:pointer;
        user-select:none;
        font-weight:600;
    }

    .nav-group-title{
        display:inline-block;
    }

    .nav-group-list{
        display:none;
        padding-left:0;
        margin:0;
        list-style:none;
    }

    .nav-group.open > .nav-group-list{
        display:block;
    }

    .group-arrow{
        transition: transform .2s ease;
    }

    .nav-group.open .group-arrow{
        transform: rotate(90deg);
    }

    /* optional: small spacing so it looks like your old nav-header */
    .nav-group{
        margin-top: 6px;
    }
</style>

<div id="sidebar" style="overflow-y:auto;height:100vh;"
     class="left-sidebar fixed-sidebar bg-black-300 box-shadow tour-three">
    <div class="sidebar-content">

        {{-- user info --}}
        <div class="user-info closed">
            @if ($user)
                <img src="{{ asset('images/uploads/'.$user->profile) }}"
                     alt="{{ $user->name }} {{ $user->surname }}"
                     class="img-circle profile-img" style="width:90px;height:90px">
            @endif
            <h6 class="title">{{ $user?->name }} {{ $user?->surname }}</h6>
            <small class="info">{{ $employee?->post }}</small>
        </div>

        <div class="sidebar-nav">
            <ul class="side-nav color-gray">

                @foreach($menuGroups as $group)

                    @php
                        // ✅ group active + visibility
                        $groupVis = $json($group->visibility);

                        if (!($group->is_active ?? true)) {
                            continue;
                        }

                        if (!Menu::isVisible($groupVis, $menuCtx)) {
                            continue;
                        }

                        // ✅ only keep modules that are active AND visible (with inherited visibility)
                        $visibleModules = $group->modules
                            ->sortBy('sort_order')
                            ->filter(function ($m) use ($groupVis, $inheritVis, $menuCtx) {
                                if (!($m->is_active ?? true)) return false;

                                $mVis = $inheritVis($m->visibility, $groupVis);
                                return Menu::isVisible($mVis, $menuCtx);
                            });

                        // ✅ if group has nothing to show, skip header
                        if ($visibleModules->isEmpty()) {
                            continue;
                        }

                        // ✅ determine if this group contains the active route (module or any submodule)
                        $groupActive = $visibleModules->contains(function ($m) {
                            if (Menu::isActive($m->route_name, $m->slug)) return true;

                            foreach ($m->sub_modules ?? [] as $s) {
                                if (Menu::isActive($s->route_name, $s->slug)) return true;
                            }
                            return false;
                        });

                        $groupKey = 'mg_' . $group->id;
                    @endphp

                    {{-- ✅ Collapsible Module Group --}}
                    <li class="nav-group {{ $groupActive ? 'open' : '' }}"
                        data-group="{{ $groupKey }}"
                        data-active="{{ $groupActive ? 1 : 0 }}">

                        <a href="javascript:void(0)"
                           class="nav-group-toggle"
                           aria-expanded="{{ $groupActive ? 'true' : 'false' }}">
                            <span class="nav-group-title">{{ $group->name }}</span>
                            <i class="fas fa-angle-right group-arrow"></i>
                        </a>

                        <ul class="nav-group-list">

                            @foreach($visibleModules as $module)

                                @php
                                    // ✅ module effective visibility (inherit from group if null)
                                    $moduleVis = $inheritVis($module->visibility, $groupVis);

                                    // ✅ visible + active submodules (inherit from module if null)
                                    $visibleSubs = $module->sub_modules
                                        ->sortBy('sort_order')
                                        ->filter(function ($s) use ($moduleVis, $inheritVis, $menuCtx) {
                                            if (!($s->is_active ?? true)) return false;

                                            $sVis = $inheritVis($s->visibility, $moduleVis);
                                            return Menu::isVisible($sVis, $menuCtx);
                                        });

                                    $hasChildren = $visibleSubs->count() > 0;

                                    // ✅ URLs
                                    $moduleParams = Menu::resolveRouteParams($routeParams($module->route_params), $menuCtx);
                                    $moduleHref   = Menu::href($module->route_name, $module->url, $moduleParams);

                                    // If module is just a container, keep it non-clickable
                                    $parentHref = ($hasChildren && empty($module->route_name) && empty($module->url))
                                        ? 'javascript:void(0)'
                                        : $moduleHref;

                                    // ✅ Active state
                                    $moduleActive = Menu::isActive($module->route_name, $module->slug);

                                    if ($hasChildren) {
                                        foreach ($visibleSubs as $sub) {
                                            if (Menu::isActive($sub->route_name, $sub->slug)) {
                                                $moduleActive = true;
                                                break;
                                            }
                                        }
                                    }

                                    // ✅ badges
                                    $badgeKey = is_string($module->badge_key) ? $module->badge_key : null;
                                    $badgeVal = $badgeKey ? (int) ($menuBadges[$badgeKey] ?? 0) : 0;
                                @endphp

                                @if($hasChildren)
                                    <li class="has-children {{ $moduleActive ? 'active' : '' }}">
                                        <a href="{{ $parentHref }}">
                                            @if($module->icon)<i class="{{ $module->icon }}"></i>@endif
                                            <span>{{ $module->name }}</span>

                                            @if($badgeVal > 0)
                                                <span class="label label-success ml-5">{{ $badgeVal }}</span>
                                            @endif

                                            <i class="fas fa-angle-right arrow"></i>
                                        </a>

                                        <ul class="child-nav {{ $moduleActive ? 'show' : '' }}">
                                            @foreach($visibleSubs as $sub)
                                                @php
                                                    $subParams = Menu::resolveRouteParams($routeParams($sub->route_params), $menuCtx);
                                                    $subHref   = Menu::href($sub->route_name, $sub->url, $subParams);
                                                    $subActive = Menu::isActive($sub->route_name, $sub->slug);

                                                    $subBadgeKey = is_string($sub->badge_key) ? $sub->badge_key : null;
                                                    $subBadgeVal = $subBadgeKey ? (int) ($menuBadges[$subBadgeKey] ?? 0) : 0;
                                                @endphp

                                                <li class="{{ $subActive ? 'active' : '' }}">
                                                    <a href="{{ $subHref }}">
                                                        @if($sub->icon)<i class="{{ $sub->icon }}"></i>@endif
                                                        <span>{{ $sub->name }}</span>

                                                        @if($subBadgeVal > 0)
                                                            <span class="label label-success ml-5">{{ $subBadgeVal }}</span>
                                                        @endif
                                                    </a>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </li>
                                @else
                                    <li class="{{ $moduleActive ? 'active' : '' }}">
                                        <a href="{{ $moduleHref }}">
                                            @if($module->icon)<i class="{{ $module->icon }}"></i>@endif
                                            <span>{{ $module->name }}</span>

                                            @if($badgeVal > 0)
                                                <span class="label label-success ml-5">{{ $badgeVal }}</span>
                                            @endif
                                        </a>
                                    </li>
                                @endif

                            @endforeach

                        </ul>
                    </li>
                @endforeach

            </ul>
        </div>
    </div>
</div>

    <script>
document.addEventListener('DOMContentLoaded', function () {
    const groups = document.querySelectorAll('.nav-group');

    groups.forEach(group => {
        const key = group.dataset.group;
        const isActive = group.dataset.active === '1';
        const storageKey = 'sidebar_group_' + key;

        // Restore saved state (but active route forces open)
        if (!isActive) {
            const saved = localStorage.getItem(storageKey);
            if (saved === 'open') group.classList.add('open');
            if (saved === 'closed') group.classList.remove('open');
        } else {
            group.classList.add('open');
        }

        const toggle = group.querySelector('.nav-group-toggle');
        if (!toggle) return;

        toggle.addEventListener('click', function (e) {
            e.preventDefault();

            group.classList.toggle('open');

            const openNow = group.classList.contains('open');
            toggle.setAttribute('aria-expanded', openNow ? 'true' : 'false');
            localStorage.setItem(storageKey, openNow ? 'open' : 'closed');
        });
    });
});
</script>
