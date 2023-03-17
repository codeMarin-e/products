<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\ProductRequest;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Support\Facades\View;
use App\Http\Controllers\Controller;
use Illuminate\Support\Str;
use Illuminate\Http\Request;

class ProductController extends Controller {
    public function __construct() {
        if(!request()->route()) return;
        $this->middleware(function($request, \Closure $next) {
            $chCategory = request()->route('chCategory');
            $chProduct = request()->route('chProduct');
            if(!$chProduct->getCategoryProduct($chCategory)) abort(404);
            return $next($request);
        })->only('edit', 'show', 'update', 'destroy', 'move');

        $this->categories_table = Category::getModel()->getTable();
        $this->db_table = Product::getModel()->getTable();
        $this->routeNamespace = Str::before(request()->route()->getName(), '.categories.products');
        View::composer('admin/products/*', function($view)  {
            $viewData = [
                'route_namespace' => $this->routeNamespace,
            ];
            // @HOOK_VIEW_COMPOSERS
            $view->with($viewData);
        });
        // @HOOK_CONSTRUCT
    }

    public function index(Category $chCategory) {
        $viewData = ['chCategory' => $chCategory ];
        $viewData['products'] = $chCategory->products()->where("{$this->db_table}.site_id", app()->make('Site')->id);

        // @HOOK_INDEX_END

        $viewData['products'] = $viewData['products']->paginate(20)->appends( request()->query() );

        return view('admin/products/products', $viewData);
    }

    public function create(Category $chCategory) {
        $viewData = ['chCategory' => $chCategory ];
        $categoriesQry = Category::where("{$this->categories_table}.site_id", app()->make('Site')->id)->orderBy("{$this->categories_table}.ord", 'ASC');
        $subCategoriesQry = clone $categoriesQry;

        // @HOOK_CREATE

        $viewData['subCategoriesQry'] = $subCategoriesQry;
        $viewData['categories'] = $categoriesQry->where("{$this->categories_table}.parent_id", 0)->get();
        return view('admin/products/product', $viewData);
    }

    public function edit(Category $chCategory, Product $chProduct) {
        $viewData = ['chCategory' => $chCategory ];
        $viewData['chProduct'] = $chProduct;
        $categoriesQry = Category::where("{$this->categories_table}.site_id", app()->make('Site')->id)->orderBy("{$this->categories_table}.ord", 'ASC');
        $subCategoriesQry = clone $categoriesQry;

        // @HOOK_EDIT

        $viewData['subCategoriesQry'] = $subCategoriesQry;
        $viewData['categories'] = $categoriesQry->where("{$this->categories_table}.parent_id", 0)->get();
        return view('admin/products/product', $viewData);
    }

    public function store(Category $chCategory, ProductRequest $request) {
        $validatedData = $request->validated();

        // @HOOK_STORE_VALIDATE

        $chProduct = Product::create( array_merge([
            'site_id' => app()->make('Site')->id,
        ], $validatedData));

        // @HOOK_STORE_INSTANCE

        $chProduct->setAVars($validatedData['add']);
        $chProduct->categories()->sync( $validatedData['categories'] );
        $chProduct->reAttachAndOrder( $validatedData['pictures'] ?? [], 'pictures' );
        $chProduct->setUri($validatedData['uri']['slug'], $validatedData['uri']['pointable_type'], $validatedData['uri']['attributes']);

        // @HOOK_STORE_END
        event( 'product.submited', [$chProduct, $validatedData] );

        return redirect()->route($this->routeNamespace.'.categories.products.edit',
            [(in_array($chCategory->id, $validatedData['categories'])? $chCategory : reset($validatedData['categories'])), $chProduct])
            ->with('message_success', trans('admin/products/product.created'));
    }

    public function update(Category $chCategory, Product $chProduct, ProductRequest $request) {
        $validatedData = $request->validated();

        // @HOOK_UPDATE_VALIDATE

        $chProduct->update( $validatedData );
        $chProduct->setAVars($validatedData['add']);
        $chProduct->categories()->sync( $validatedData['categories'] );
        $chProduct->reAttachAndOrder( $validatedData['pictures'] ?? [], 'pictures' );
        $chProduct->setUri($validatedData['uri']['slug'], $validatedData['uri']['pointable_type'], $validatedData['uri']['attributes']);

        // @HOOK_UPDATE_END

        event( 'product.submited', [$chProduct, $validatedData] );

        if($request->has('action')) {
            return redirect()->route($this->routeNamespace.'.categories.products.index',
                [(in_array($chCategory->id, $validatedData['categories'])? $chCategory : reset($validatedData['categories']))])
                ->with('message_success', trans('admin/products/product.updated'));
        }
        return redirect()->route($this->routeNamespace . '.categories.products.edit',
            [(in_array($chCategory->id, $validatedData['categories'])? $chCategory : reset($validatedData['categories'])), $chProduct])
            ->with('message_success', trans('admin/products/product.updated'));
    }

    public function move(Category $chCategory, Product $chProduct, $direction) {
        // @HOOK_MOVE

        $chProduct->orderMove($chCategory, $direction);

        // @HOOK_MOVE_END

        return back();
    }

    public function restore(Category $chCategory,Product $chProductTrashed) {

        $chProductTrashed->restore();

        return back();
    }

    public function destroy(Category $chCategory, Product $chProduct, Request $request) {
        // @HOOK_DESTROY

        $chProduct->delete();

        // @HOOK_DESTROY_END

        if($request->redirect_to)
            return redirect()->to($request->redirect_to)
                ->with('message_danger', trans('admin/products/product.deleted'));

        return redirect()->route($this->routeNamespace.'.categories.products.index', [$chCategory])
            ->with('message_danger', trans('admin/products/product.deleted'));
    }
}
