@php $inputBag = 'product'; @endphp

@pushonce('above_css')
    <link href="{{ asset('admin/vendor/chosen1.8.7/bootstrap4.chosen.min.css') }}" rel="stylesheet" type="text/css"/>
@endpushonce

@pushonce('below_js')
    <script type="text/javascript" src="{{ asset('admin/vendor/chosen1.8.7/chosen.jquery.min.js') }}"></script>
@endpushonce

@pushonceOnReady('below_js_on_ready')
<script>
    $("#{{$inputBag}}\\[categories\\]\\[\\]").chosen({
        placeholder_text_multiple:  '@lang('admin/products/product.choose_categories')',
        no_results_text:  '@lang('admin/products/product.choose_categories_no_results')',
        width:"100%"
    });
</script>
@endpushonceOnReady

@pushonce('below_templates')
@if(isset($chProduct) && $authUser->can('delete', $chProduct))
    <form action="{{ route("{$route_namespace}.categories.products.destroy", [$chCategory, $chProduct]) }}"
          method="POST"
          id="delete[{{$chProduct->id}}]">
        @csrf
        @method('DELETE')
    </form>
@endif
@endpushonce

{{-- @HOOK_AFTER_PUSHES --}}

<x-admin.main>
    <div class="container-fluid">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{route("{$route_namespace}.home")}}"><i class="fa fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="{{ route("{$route_namespace}.categories.index") }}">@lang('admin/categories/categories.categories')</a></li>
            @foreach($chCategory->getParents() as $parentCategory)
                <li class="breadcrumb-item"><a href="{{ route("{$route_namespace}.categories.products.index", [$parentCategory]) }}">{{$parentCategory->aVar('name')}}</a></li>
            @endforeach
            <li class="breadcrumb-item"><a href="{{ route("{$route_namespace}.categories.products.index", [$chCategory]) }}">{{$chCategory->aVar('name')}}</a></li>
            <li class="breadcrumb-item active">@isset($chProduct){{ $chProduct->aVar('name') }}@else @lang('admin/products/product.create') @endisset</li>
        </ol>

        <div class="card">
            <div class="card-body">
                <form action="@isset($chProduct){{ route("{$route_namespace}.categories.products.update", [$chCategory, $chProduct]) }}@else{{ route("{$route_namespace}.categories.products.store", [$chCategory]) }}@endisset"
                      method="POST"
                      autocomplete="off"
                      enctype="multipart/form-data">
                    @csrf
                    @isset($chProduct)@method('PATCH')@endisset

                    <x-admin.box_messages />

                    <x-admin.box_errors :inputBag="$inputBag" />

                    @php
                        $sCategoryIds = old("{$inputBag}.categories", (isset($chProduct)? $chProduct->categories->pluck('id')->toArray() : [$chCategory->id]));
                    @endphp
                    <div class="form-group row">
                        <label for="{{$inputBag}}[categories][]"
                               class="col-lg-2 col-form-label">@lang('admin/products/product.categories'):</label>
                        <div class="col-lg-4">
                            <select class="form-control @if($errors->$inputBag->has('categories')) is-invalid @endif"
                                    multiple="multiple"
                                    id="{{$inputBag}}[categories][]"
                                    name="{{$inputBag}}[categories][]">
                                @includeWhen($categories->count(), 'admin/products/categories_options', [
                                    'mainCategories' => $categories,
                                    'sCategoryIds' => $sCategoryIds,
                                    'level' => 0
                                ])
                            </select>
                        </div>
                    </div>
                    {{-- @HOOK_AFTER_CATEGORY --}}

                    <div class="form-group row">
                        <label for="{{$inputBag}}[add][name]"
                               class="col-lg-2 col-form-label"
                        >@lang('admin/products/product.name'):</label>
                        <div class="col-lg-10">
                            <input type="text"
                                   name="{{$inputBag}}[add][name]"
                                   id="{{$inputBag}}[add][name]"
                                   value="{{ old("{$inputBag}.add.name", (isset($chProduct)? $chProduct->aVar('name') : '')) }}"
                                   class="form-control @if($errors->$inputBag->has('add.name')) is-invalid @endif"
                            />
                        </div>
                    </div>
                    {{-- @HOOK_AFTER_NAME --}}

                    <div class="form-group row">
                        <label for="{{$inputBag}}[add][description]"
                               class="col-lg-2 col-form-label @if($errors->$inputBag->has('add.description')) text-danger @endif"
                        >@lang('admin/products/product.description'):</label>
                        <div class="col-lg-10">
                            <x-admin.editor
                                :inputName="$inputBag.'[add][description]'"
                                :otherClasses="[ 'form-controll', ]"
                            >{{old("{$inputBag}.add.content", (isset($chProduct)? $chProduct->aVar('description') : ''))}}</x-admin.editor>
                        </div>
                    </div>
                    {{-- @HOOK_AFTER_DESCRIPTION--}}

                    <x-admin.filepond
                        translations="admin/products/product.pictures"
                        :routeNamespace="$route_namespace"
                        type="pictures"
                        :inputBag="$inputBag"
                        :accept="'[\'image/*\']'"
                        maxFileSize="1MB"
                        :multiple="true"
                        :attachable="$chProduct?? null"
                    />
                    {{-- @HOOK_AFTER_PICTURES--}}

                    @php $additionals = ['category' => $chCategory]; @endphp
                    <x-admin.uriable
                        :inputBag="$inputBag"
                        :uriable="$chProduct?? null"
                        :defaultUri="isset($chProduct)? $chProduct->defaultUri(additionals: $additionals) : $chCategory->getUriSlug().'/products/[id]'"
                    />
                    {{-- @HOOK_AFTER_URIABLE --}}

                    <div class="form-group row form-check">
                        <div class="col-lg-6">
                            <input type="checkbox"
                                   value="1"
                                   id="{{$inputBag}}[active]"
                                   name="{{$inputBag}}[active]"
                                   class="form-check-input @if($errors->$inputBag->has('active'))is-invalid @endif"
                                   @if(old("{$inputBag}.active") || (is_null(old("{$inputBag}.active")) && isset($chProduct) && $chProduct->active ))checked="checked"@endif
                            />
                            <label class="form-check-label"
                                   for="{{$inputBag}}[active]">@lang('admin/products/product.active')</label>
                        </div>
                    </div>
                    {{-- @HOOK_AFTER_ACTIVE --}}

                    <div class="form-group row">
                        @isset($chProduct)
                            @can('update', $chProduct)
                                <button class='btn btn-success mr-2'
                                        type='submit'
                                        name='action'>@lang('admin/products/product.save')
                                </button>

                                <button class='btn btn-primary mr-2'
                                        type='submit'
                                        name='update'>@lang('admin/products/product.update')</button>
                            @endcan

                            @can('delete', $chProduct)
                                <button class='btn btn-danger mr-2'
                                        type='button'
                                        onclick="if(confirm('@lang("admin/products/product.delete_ask")')) document.querySelector( '#delete\\[{{$chProduct->id}}\\] ').submit() "
                                        name='delete'>@lang('admin/products/product.delete')</button>
                            @endcan
                        @else
                            <button class='btn btn-success mr-2'
                                    type='submit'
                                    name='create'>@lang('admin/products/product.create')</button>
                        @endisset

                        {{-- @HOOK_AFTER_BUTTONS --}}

                        <a class='btn btn-warning'
                           href="{{ route("{$route_namespace}.categories.products.index", [$chCategory]) }}"
                        >@lang('admin/products/product.cancel')</a>
                    </div>

                    {{-- @HOOK_ADDON_BUTTONS --}}
                </form>
            </div>
        </div>

        {{-- @HOOK_ADDONS --}}
    </div>
</x-admin.main>
