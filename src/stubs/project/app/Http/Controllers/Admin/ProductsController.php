<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\ProductRequest;
use App\Models\AddVar;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Support\Facades\View;
use App\Http\Controllers\Controller;
use Illuminate\Support\Str;
use Illuminate\Http\Request;

class ProductsController extends Controller {
    public function __construct() {
        if(!request()->route()) return;
        $this->categories_table = Category::getModel()->getTable();
        $this->add_table = AddVar::getModel()->getTable();
        $this->db_table = Product::getModel()->getTable();
        $this->routeNamespace = Str::before(request()->route()->getName(), '.products');
        View::composer('admin/products/*', function($view)  {
            $viewData = [
                'route_namespace' => $this->routeNamespace,
            ];
            // @HOOK_VIEW_COMPOSERS
            $view->with($viewData);
        });
        // @HOOK_CONSTRUCT
    }

    private function getSubCategoriesRe($category) {
        $category->loadMissing('children');
        $return[] = $category->id;
        foreach($category->children as $subCategory) {
            $return = array_merge($return, $this->getSubCategoriesRe($subCategory));
        }
        return $return;
    }

    public function index() {
        $viewData = [];
        $categoriesQry = Category::where("{$this->categories_table}.site_id", app()->make('Site')->id)->orderBy("{$this->categories_table}.ord", 'ASC');
        $subCategoriesQry = clone $categoriesQry;

        $bldQry = Product::select("{$this->db_table}.*")
            ->where("{$this->db_table}.site_id", app()->make('Site')->id)
            ->join($this->add_table, function($join) { //MAY CHANGE A BIT TO USE FALLBACKS, TOO
                $join->on("{$this->add_table}.addvariable_id", '=', "{$this->db_table}.id")
                    ->where("{$this->add_table}.site_id", app()->make('Site')->id)
                    ->where("{$this->add_table}.language", app()->getLocale())
                    ->where("{$this->add_table}.addvariable_type", '=', Product::class)
                    ->where("{$this->add_table}.var_name", 'name');
            })
            ->with(['categories']);

        if($filters = request()->get('filters')) {
            //BY CATEGORY
            if(isset($filters['category'])) {
                if($filters['category'] == 'all') {
                    $routeQry = request()->query();
                    unset($routeQry['filters']['category']);
                    return redirect( now_route(null, $routeQry) );
                }
                $filterCategoryId = (int)$filters['category'];
                if($filterCategory = Category::with('children')->find($filterCategoryId)) {
                    $filterCategoryIds = $this->getSubCategoriesRe($filterCategory);
                    $bldQry->whereHas('categories', function($qry) use ($filterCategoryIds) {
                        $qry->whereIn("{$this->categories_table}.id", $filterCategoryIds);
                    });
                }
                $viewData['filters']['category'] = $filterCategoryId;
            }
            //END BY CATEGORY

            // @HOOK_FILTERS
        }

        //ORDER BY
        $orderByArr = [
            'name',
            'id',

            // @HOOK_ORDER_BY_TYPES
        ];
        $orderBy = reset($orderByArr);
        $orderByDir = 'ASC';
        if($orderGet = request()->get('order')) {
            if(in_array($orderGet, $orderByArr)) {
                $orderBy = $orderGet;
            }
        }
        if(request()->get('dir')) {
            $orderByDir = 'DESC';
        }
        switch($orderBy) {
            case 'name':
                $bldQry = $bldQry->orderBy("{$this->add_table}.var_value", $orderByDir);
                break;
            case 'id':
                $bldQry = $bldQry->orderBy("{$this->db_table}.id", $orderByDir);
                break;
            // @HOOK_ORDER_BY_TYPE_CALL
        }
        $viewData['orderBy'] = $orderBy;
        $viewData['orderByDir'] = $orderByDir;
        // @HOOK_ORDER_BY_END
        //END ORDER BY

        //SEARCHING
        if(request()->has('search')) {
            $search = trim(request()->get('search'));
            if($search != '') {
                $bldQry = $bldQry->where(function($qry2) use ($search) {
                    $qry2->where("{$this->db_table}.id", '=', (int)$search)
                        ->orWhere(function($qry3) use ($search) {
                            $searchParts = explode(' ', $search);
                            foreach($searchParts as $searchIndex => $searchPart) {
                                $qry3 = $searchIndex?
                                    $qry3->orWhere("{$this->add_table}.var_value", 'like', "%{$searchPart}%") :
                                    $qry3->where("{$this->add_table}.var_value", 'like', "%{$searchPart}%");
                            }
                        });

                    // @HOOK_SEARCHING_BY
                });
                $viewData['search'] = $search;
            } else {
                return redirect()->route($this->routeNamespace.'.products.index');
            }
            // @HOOK_SEARCHING_END
        }
        //END SEARCHING

        // @HOOK_INDEX_END

        $viewData['subCategoriesQry'] = $subCategoriesQry;
        $viewData['categories'] = $categoriesQry->where("{$this->categories_table}.parent_id", 0)->get();
        $viewData['products'] = $bldQry->paginate(20)->appends( request()->query() );

        return view('admin/products/products_all', $viewData);
    }
}
