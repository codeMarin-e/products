<x-admin.main>
    <div class="container-fluid">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{route("{$route_namespace}.home")}}"><i class="fa fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="{{ route("{$route_namespace}.categories.index") }}">@lang('admin/categories/categories.categories')</a></li>
            @foreach($chCategory->getParents() as $parentCategory)
                <li class="breadcrumb-item"><a href="{{ route("{$route_namespace}.categories.products.index", [$parentCategory]) }}">{{$parentCategory->aVar('name')}}</a></li>
            @endforeach
            <li class="breadcrumb-item"><a href="{{ route("{$route_namespace}.categories.products.index", [$chCategory]) }}">{{$chCategory->aVar('name')}}</a></li>
            <li class="breadcrumb-item active">@lang('admin/products/products.products')</li>
        </ol>

        @can('create', App\Models\Product::class)
            <a href="{{ route("{$route_namespace}.categories.products.create", [$chCategory]) }}"
               class="btn btn-sm btn-primary h5"
               title="create">
                <i class="fa fa-plus mr-1"></i>@lang('admin/products/products.create')
            </a>
        @endcan

        {{-- @HOOK_AFTER_CREATE --}}

        <x-admin.box_messages />

        <div class="table-responsive rounded ">
            <table class="table table-sm">
                <thead class="thead-light">
                <tr class="">
                    <th scope="col" class="text-center">@lang('admin/products/products.id')</th>
                    {{-- @HOOK_AFTER_ID_TH --}}

                    <th scope="col" class="w-75">@lang('admin/products/products.name')</th>
                    {{-- @HOOK_AFTER_NAME_TH --}}

                    <th scope="col" class="text-center">@lang('admin/products/products.edit')</th>
                    {{-- @HOOK_AFTER_EDIT_TH --}}

                    <th colspan="2" scope="col" class="text-center">@lang('admin/products/products.move_th')</th>
                    {{-- @HOOK_AFTER_MOVE_TH --}}

                    <th scope="col" class="text-center">@lang('admin/products/products.remove')</th>
                </tr>
                </thead>
                <tbody>
                @forelse($products as $product)
                    @php
                        $productEditUri = route("{$route_namespace}.categories.products.edit", [$chCategory, $product]);
                        $canUpdate = $authUser->can('update', $product);
                    @endphp
                    @if($loop->first)
                        @php $prevProduct = $product->getPrevious($chCategory); @endphp
                    @endif
                    @if($loop->last)
                        @php $nextProduct = $product->getNext($chCategory); @endphp
                    @endif
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

                        {{--    MOVE DOWN    --}}
                        <td class="text-center align-middle">
                            @if($canUpdate && (!$loop->last || $nextProduct))
                                <a class="btn btn-link text-dark"
                                   href="{{route("{$route_namespace}.categories.products.move", [$chCategory, $product, 'down'])}}"
                                   title="@lang('admin/products/products.move_down')"><i class="fa fa-arrow-down"></i></a>
                            @endif
                        </td>

                        {{--    MOVE UP   --}}
                        <td class="text-center align-middle">
                            @if($canUpdate && (!$loop->first || $prevProduct))
                                <a class="btn btn-link text-dark"
                                   href="{{route("{$route_namespace}.categories.products.move", [$chCategory, $product,'up'])}}"
                                   title="@lang('admin/products/products.move_up')"><i class="fa fa-arrow-up"></i></a>
                            @endif
                        </td>
                        {{-- @HOOK_AFTER_MOVE--}}

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
                                    <input type="hidden" name="redirect_to" value="{{$redirectTo}}" />
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
                        <td colspan="100%">@lang('admin/products/products.no_products')</td>
                    </tr>
                @endforelse
                </tbody>
            </table>

            {{$products->links('admin.paging')}}

        </div>
    </div>
</x-admin.main>
