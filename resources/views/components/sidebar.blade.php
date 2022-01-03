<div class="aside aside-left  aside-fixed  d-flex flex-column flex-row-auto" id="kt_aside">


    <div class="brand flex-column-auto" id="kt_brand">
        <div class="brand-logo">
            @if (config('settings.logo'))
            <a href="{{ url('dashboard') }}">
                <img src="{{ asset('storage/'.LOGO_PATH.config('settings.logo'))}}" style="max-width: 80px;" alt="Logo" />
            </a>
            @else
            <h3 class="text-white m-0" style="font-size: 12px;font-weight:bold;">{{ config('settings.title') ? config('settings.title') : env('APP_NAME') }}</h3>
            @endif
        </div>
    </div>


    <div class="aside-menu-wrapper flex-column-fluid" id="kt_aside_menu_wrapper">


        <div id="kt_aside_menu" class="aside-menu my-4 " data-menu-vertical="1" data-menu-scroll="1" data-menu-dropdown-timeout="500">
            <ul class="menu-nav ">
                @if(Session::get('user_menu'))
                @foreach (Session::get('user_menu') as $menu)
                    @if($menu->children->isEmpty())
                        @if ($menu->type == 1)
                            <li class="menu-section ">
                                <h4 class="menu-text">{{ $menu->divider_title }}</h4>
                            </li>
                        @else 
                            <li class="menu-item  {{ (request()->is($menu->url)) ? 'menu-item-active' : '' }}" aria-haspopup="true">
                                <a href="{{ $menu->url ? url($menu->url) : '' }}" class="menu-link" target="{{ $menu->target ?? '_self' }}">
                                    <span class="svg-icon menu-icon"><i class="{{ $menu->icon_class }}"></i></span>
                                    <span class="menu-text">{{ $menu->module_name }}</span>
                                </a>
                            </li>
                        @endif
                    @else 
                        <li class="menu-item  menu-item-submenu 
                        @foreach ($menu->children as $submenu)
                            {{ (request()->is($submenu->url)) ? 'menu-item-open' : '' }}
                            @if(!$submenu->children->isEmpty())
                                @foreach ($submenu->children as $sub_submenu)
                                {{ (request()->is($sub_submenu->url)) ? 'menu-item-open' : '' }}
                                @endforeach
                            @endif
                        @endforeach
                        " aria-haspopup="true" data-menu-toggle="hover">
                            <a href="javascript:void();" class="menu-link menu-toggle">
                                <span class="svg-icon menu-icon"><i class="{{ $menu->icon_class }}"></i></span>
                                <span class="menu-text">{{ $menu->module_name }}</span>
                                <i class="menu-arrow"></i>
                            </a>
                            <div class="menu-submenu ">
                                <span class="menu-arrow"></span>
                                <ul class="menu-subnav">
                                    @foreach ($menu->children as $submenu)
                                        @if($submenu->children->isEmpty())
                                            <li class="menu-item {{ (request()->is($submenu->url)) ? 'menu-item-active' : '' }}" aria-haspopup="true">
                                                <a href="{{ $submenu->url ? url($submenu->url) : '' }}" class="menu-link ">
                                                    <i class="menu-bullet menu-bullet-dot"><span></span></i>
                                                    <span class="menu-text">{{ $submenu->module_name }}</span>
                                                </a>
                                            </li>
                                        @else
                                        <!-- -->
                                            <li class="menu-item  menu-item-submenu 
                                                @foreach ($submenu->children as $sub_submenu)
                                                {{ (request()->is($sub_submenu->url)) ? 'menu-item-open' : '' }}
                                                @endforeach
                                                " aria-haspopup="true" data-menu-toggle="hover">
                                                <a href="javascript:void();" class="menu-link menu-toggle">
                                                    <i class="menu-bullet menu-bullet-dot"><span></span></i>
                                                    <span class="menu-text">{{ $submenu->module_name }}</span>
                                                    <i class="menu-arrow"></i>
                                                </a>
                                                <div class="menu-submenu ">
                                                    <span class="menu-arrow"></span>
                                                    <ul class="menu-subnav">
                                                        @foreach ($submenu->children as $sub_submenu)
                                                        <li class="menu-item {{ (request()->is($sub_submenu->url)) ? 'menu-item-active' : '' }}" aria-haspopup="true">
                                                            <a href="{{ $sub_submenu->url ? url($sub_submenu->url) : '' }}" class="menu-link ">
                                                                <i class="menu-bullet menu-bullet-dot"><span></span></i>
                                                                <span class="menu-text">{{ $sub_submenu->module_name }}</span>
                                                            </a>
                                                        </li>
                                                        @endforeach
                                                    </ul>
                                                </div>
                                            </li>
                                        @endif
                                        <!-- -->

                                    @endforeach
                                </ul>
                            </div>
                        </li>
                    @endif
                @endforeach
                @endif
            </ul>
        </div>
    </div>

</div>