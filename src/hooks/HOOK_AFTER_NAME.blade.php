<td class="align-middle text-center text-primary">
    <a href="{{ route("{$route_namespace}.categories.products.index", [$category]) }}"
       title="@lang('admin/products/products.products')">
        <span class="badge badge-secondary">@lang('admin/products/products.products')</span>
        <span class="badge badge-primary">{{$category->products->count()}}</span>
    </a>
</td>
