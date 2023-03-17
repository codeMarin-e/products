@foreach($mainCategories as $category)
    <option value="{{$category->id}}"
            @if(in_array($category->id, $sCategoryIds))selected='selected'@endif
        >{!! str_repeat('&nbsp;&nbsp;&nbsp;&nbsp;', $level) !!}{{$category->aVar('name')}}</option>
    @php $subCategories = $category->childrenQry($subCategoriesQry)->get(); @endphp
    @includeWhen( $subCategories->count(), 'admin/products/categories_options', [
        'mainCategories' => $subCategories,
        'sCategoryIds' => $sCategoryIds,
        'level' => $level+1
    ])
@endforeach
