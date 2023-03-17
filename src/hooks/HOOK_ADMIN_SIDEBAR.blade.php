@if($authUser->can('view', \App\Models\Category::class) || $authUser->can('view', \App\Models\Product::class))
    @php $groupActive = (request()->route()->named(["{$whereIam}.products.*", "{$whereIam}.categories.*"])); @endphp
    <li class="nav-item @if($groupActive) active show @endif dropdown">
        <a class="nav-link dropdown-toggle"
           href="#" id="modulesProductsDropdown"
           role="button"
           data-toggle="dropdown"
           aria-haspopup="true"
           aria-expanded="@if($groupActive) active @else false @endif">
        <i class="fas fa-fw fa-cubes"></i>
        <span>@lang('admin/products/products.sidebar_group')</span>
        </a>
        <div class="dropdown-menu @if($groupActive) show @endif" aria-labelledby="modulesProductsDropdown">
            @if($authUser->can('view', \App\Models\Category::class))
                <a class="dropdown-item" href="{{route("{$whereIam}.categories.index")}}">@lang("admin/categories/categories.sidebar")</a>
            @endif
            @if($authUser->can('view', \App\Models\Product::class))
                <a class="dropdown-item" href="{{route("{$whereIam}.products.index")}}">@lang("admin/products/products.sidebar")</a>
            @endif
            {{-- @HOOK_OPTIONS --}}
        </div>
    </li>
@endif
