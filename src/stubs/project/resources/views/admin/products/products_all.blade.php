@pushonceOnReady('below_js_on_ready')
<script>
    //CHANGE FILTER
    $(document).on('change', '.js_filter', function(e) {
        var $this = $(this);
        var $thisVal = $this.val();
        if($thisVal == 'all') {
            window.location.href= $this.attr('data-action_all')
            return;
        }
        window.location.href= $this.attr('data-action').replace('__VAL__', $this.val());
    });
</script>
@endpushonceOnReady

<x-admin.main>
    <div class="container-fluid">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{route("{$route_namespace}.home")}}"><i class="fa fa-home"></i></a></li>
            <li class="breadcrumb-item active">@lang('admin/products/products_all.products')</li>
        </ol>
        <form autocomplete="off">
            <div class="row">
                {{-- CATEGORIES --}}
                @php
                    $sCategoryIds = isset($filters['category'])? [$filters['category']] : [];
                @endphp
                <div class="form-group row col-lg-4">
                    <label for="filters[category]" class="col-form-label col-sm-3">@lang('admin/products/products_all.filter_category.label'):</label>
                    <div class="col-sm-9">
                        <select id="filters[category]"
                                name="filters[category]"
                                data-action_all="{{marinarFullUrlWithQuery( ['filters' => ['category' => null]] )}}"
                                data-action="{{marinarFullUrlWithQuery( ['filters' => ['category' => '__VAL__']] )}}"
                                class="form-control js_filter">
                            <option value='all'>@lang('admin/products/products_all.filter_category.all')</option>
                            @includeWhen($categories->count(), 'admin/products/categories_options', [
                                    'mainCategories' => $categories,
                                    'sCategoryIds' => $sCategoryIds,
                                    'level' => 0
                                ])
                        </select>
                    </div>
                </div>
                {{-- END CATEGORIES --}}
                {{-- @HOOK_AFTER_CATEGORIES_FILTER--}}

                {{-- SEARCH --}}
                <div class="form-group row col-lg-3">
                    <div class="col-sm-10">
                        <div class="input-group">
                            <input type="text"
                                   name="search"
                                   id="search"
                                   placeholder="@lang('admin/products/products_all.search')"
                                   value="@if(isset($search)){{$search}}@endif"
                                   class="form-control "
                            />
                            <div class="input-group-append">
                                <button class="btn btn-primary"><i class="fas fa-search text-grey"
                                                                   aria-hidden="true"></i></button>
                            </div>
                        </div>
                    </div>
                </div>
                {{-- END SEARCH --}}
                {{-- @HOOK_AFTER_SEARCH--}}
            </div>
            {{-- @HOOK_OTHER_FILTERS --}}

        </form>



        <div class="table-responsive rounded ">
            <table class="table table-sm">
                <thead class="thead-light">
                <tr class="">
                    <th scope="col" class="text-center">
                        @php
                            $additionalQuery = ['order' => 'id'];
                            if($orderBy =='id') $additionalQuery['dir'] = ($orderByDir == 'ASC')? 1 : null;
                        @endphp
                        <a href="{{marinarFullUrlWithQuery( $additionalQuery )}}" @if($orderBy == 'id') class="text-warning" @endif>
                            @lang('admin/products/products_all.id')
                        </a>
                    </th>
                    {{-- @HOOK_AFTER_ID_TH --}}

                    <th scope="col" class="w-60">
                        @php
                            $additionalQuery = ['order' => 'name'];
                            if($orderBy =='name') $additionalQuery['dir'] = ($orderByDir == 'ASC')? 1 : null;
                        @endphp
                        <a href="{{marinarFullUrlWithQuery( $additionalQuery )}}" @if($orderBy == 'name') class="text-warning" @endif>
                            @lang('admin/products/products_all.name')
                        </a>
                    </th>
                    {{-- @HOOK_AFTER_NAME_TH --}}

                    <th scope="col" colspan="2" class="text-center">@lang('admin/products/products_all.edit')</th>
                    {{-- @HOOK_AFTER_EDIT_TH --}}
                </tr>
                </thead>
                <tbody>
                    @forelse($products as $product)
                        @php
                            $chCategory = $product->categories->first();
                            $productEditUri = route("{$route_namespace}.categories.products.edit", [$chCategory, $product]);
                            $canUpdate = $authUser->can('update', $product);
                        @endphp
                        <tr>
                            <td scope="row" class="text-center align-middle"><a href="{{ $productEditUri }}"
                                                                                title="@lang('admin/products/products.edit')"
                                >{{ $product->id }}</a></td>
                            {{-- @HOOK_AFTER_ID --}}

                            <td class="w-75 align-middle align-middle">
                                <a href="{{ $productEditUri }}"
                                   title="@lang('admin/products/products.edit')"
                                   class=" @if($product->active) text-dark @else text-danger @endif"
                                >{{ \Illuminate\Support\Str::words($product->aVar('name'), 12,'....') }}</a>
                            </td>
                            {{-- @HOOK_AFTER_NAME --}}

                            {{--    EDIT    --}}
                            <td class="text-center align-middle">
                                <a class="btn btn-link text-success"
                                   href="{{ $productEditUri }}"
                                   title="@lang('admin/products/products.edit')"><i class="fa fa-edit"></i></a></td>
                            {{-- @HOOK_AFTER_EDIT --}}

                            {{--    DELETE    --}}
                            <td class="text-center align-middle">
                                @can('delete', $product)
                                    <form action="{{ route("{$route_namespace}.categories.products.destroy", [$chCategory, $product]) }}"
                                          method="POST"
                                          id="delete[{{$product->id}}]">
                                        @csrf
                                        @method('DELETE')
                                        @php
                                            $redirectTo = (!$products->onFirstPage() && $products->count() == 1)?
                                                    $products->previousPageUrl() :
                                                    url()->full();
                                        @endphp
                                        <button class="btn btn-link text-danger"
                                                title="@lang('admin/products/products.remove')"
                                                onclick="if(confirm('@lang("admin/products/products.remove_ask")')) document.querySelector( '#delete\\[{{$product->id}}\\] ').submit() "
                                                type="button"><i class="fa fa-trash"></i></button>
                                    </form>
                                @endcan
                            </td>
                        </tr>
                        {{-- @HOOK_AFTER_ROW --}}
                    @empty
                        <tr>
                            <td colspan="4">@lang('admin/products/products_all.no_products')</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            {{$products->links('admin.paging')}}

        </div>
    </div>
</x-admin.main>
