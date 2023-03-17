@if($authUser->can('view', \App\Models\Product::class))
    {{--   Products --}}
    <li class="nav-item @if(request()->route()->named("{$whereIam}.products.*")) active @endif">
        <a class="nav-link " href="{{route("{$whereIam}.products.index")}}">
            <i class="fa fa-fw fa-cubes mr-1"></i>
            <span>@lang("admin/products/products.sidebar")</span>
        </a>
    </li>
@endif
