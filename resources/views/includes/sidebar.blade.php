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
                    @endphp

                    <li class="nav-header">
                        <span>{{ $group->name }}</span>
                    </li>

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

                                            $subBadgeKey = $sub->badge_key;
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
                @endforeach

            </ul>
        </div>
    </div>
</div>