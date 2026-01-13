@if($companyColor)
    <style>
        .bg-black-300 {
            background-color: {{ $companyColor }};
        }
    </style>
@endif
@php
    use App\Support\Menu;
@endphp


<div id="sidebar" style="overflow-y: auto; height: 100vh;"
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
                    @if(!Menu::isVisible($group->visibility, $menuCtx))
                        @continue
                    @endif

                    <li class="nav-header">
                        <span>{{ $group->name }}</span>
                    </li>

                    @foreach($group->modules as $module)
                        @if(!Menu::isVisible($module->visibility, $menuCtx))
                            @continue
                        @endif

                        @php
                            $moduleParams = \App\Support\Menu::resolveRouteParams($module->route_params, $menuCtx);
                            $moduleHref   = Menu::href($module->route_name, $module->url, $moduleParams);
                            $hasChildren  = $module->sub_modules->count() > 0;

                            $moduleActive = Menu::isActive($module->route_name, $module->slug);

                            // if any submodule active, parent should be active/open
                            if ($hasChildren) {
                                foreach ($module->sub_modules as $sub) {
                                    if (Menu::isVisible($sub->visibility, $menuCtx) && Menu::isActive($sub->route_name, $sub->slug)) {
                                        $moduleActive = true;
                                        break;
                                    }
                                }
                            }

                            $badgeKey = $module->badge_key;
                            $badgeVal = $badgeKey ? (int) ($menuBadges[$badgeKey] ?? 0) : 0;
                        @endphp

                        @if($hasChildren)
                            <li class="has-children {{ $moduleActive ? 'active' : '' }}">
                                <a href="javascript:void(0)">
                                    @if($module->icon)<i class="{{ $module->icon }}"></i>@endif
                                    <span>{{ $module->name }}</span>

                                    @if($badgeVal > 0)
                                        <span class="label label-success ml-5">{{ $badgeVal }}</span>
                                    @endif

                                    <i class="fas fa-angle-right arrow"></i>
                                </a>

                                <ul class="child-nav {{ $moduleActive ? 'show' : '' }}">
                                    @foreach($module->sub_modules as $sub)
                                        @if(!Menu::isVisible($sub->visibility, $menuCtx))
                                            @continue
                                        @endif

                                        @php
                                            $subParams = \App\Support\Menu::resolveRouteParams($sub->route_params, $menuCtx);
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


