<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use App\Models\Category;
use App\Models\CategoryProduct;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Uriable;
use App\Traits\MacroableModel;
use App\Traits\AddVariable;
use App\Traits\Attachable;

class Product extends Model
{
    protected $fillable = ['site_id', 'parent_id', 'active'];

    use MacroableModel;
    use AddVariable;
    use SoftDeletes;

    // @HOOK_TRAITS

    //ATTACHABLE
    use Attachable;
    public static $attach_folder = 'products';
    //END ATTACHABLE

    //URIABLE
    use Uriable;
    public function defaultUri($language = null, $site_id = null, $prepareLevel = null, $additionals = []) { //just for default
        if(!($mainCategory = $this->getMainCategory($additionals['category']))) {
            return $this->id;
        }
        return $mainCategory->getUriSlug().'/products/'.$this->id;
    }

    public function prepareSlug($slug, $prepareLevel = null, $additionals = []) {
        if(!($mainCategory = $this->getMainCategory($additionals['category']))) {
            return $slug;
        }
        return $mainCategory->getUriSlug().'/products/'.$slug;
    }
    //END URIABLE

    protected static function boot() {
        parent::boot();
        static::deleting( static::class.'@onDeleting_categories' );
        static::registerModelEvent('restoring', static::class.'@onRestoring_categories' );
        static::registerModelEvent('forceDeleting', static::class.'@onForceDeleting_categories' );

        // @HOOK_BOOT
    }

    public function categories() {
        return $this->belongsToMany(Category::class, 'category_product', 'product_id', 'category_id')
            ->using(CategoryProduct::class)
            ->withPivot('ord')
            ->withTimestamps();
    }

    public function onRestoring_categories($model) {
        $model->loadMissing('categories');
        $categoryIds = $model->categories->pluck('id')->toArray();
        DB::statement( DB::raw('DELETE FROM `category_product` WHERE product_id = '.$model->id )->getValue(DB::connection()->getQueryGrammar()) );
        $model->categories()->sync($categoryIds);//just remake again
    }

    public function onDeleting_categories($model) {
        $model->loadMissing('categories');
        $categoryIds = $model->categories->pluck('id')->toArray();
        $model->categories()->sync([]);
        $model->categories()->sync($categoryIds); //to create them again
        $model->categories()->syncWithPivotValues($categoryIds, ['ord' => 0]); //just to update after onCreating_orderable
    }

    public function onForceDeleting_categories($model) {
        $model->categories()->sync([]);
    }

    public function getMainCategory($category = null) {
        $this->loadMissing('categories');
        return (is_null($category) || !$this->categories->contains($category))?
            ($this->categories->count()? $this->categories->first() : null) :
            $category;
    }

    public function getCategoryProduct($chCategory) {
        return CategoryProduct::where([
            ['category_id', $chCategory->id],
            ['product_id', $this->id]
        ])->first();
    }

    //ORDERABLE
    public function getPrevious($chCategory, $qryBld = null) {
        // @HOOK_GET_PREVIOUS
        if(!($categoryProduct = $this->getCategoryProduct($chCategory))) return;
        $qryBld = $qryBld? clone $qryBld : DB::table('category_product');
        $qryBld->join('products', "products.id", '=', "category_product.product_id")
            ->select("products.id")
            ->whereNull('products.deleted_at');
        if(!($result = $categoryProduct->getPrevious($qryBld))) return;
        // @HOOK_GET_PREVIOUS_END
        return static::find($result->id);
    }

    public function getNext($chCategory, $qryBld = null) {
        // @HOOK_GET_NEXT
        if(!($categoryProduct = $this->getCategoryProduct($chCategory))) return;
        if(!$categoryProduct) return;
        $qryBld = $qryBld? clone $qryBld : DB::table('category_product');
        $qryBld->join('products', "products.id", '=', "category_product.product_id")
            ->select("products.id")
            ->whereNull('products.deleted_at');
        if(!($result = $categoryProduct->getNext($qryBld))) return;
        // @HOOK_GET_NEXT_END
        return static::find($result->id);
    }

    public function orderMove($chCategory, $direction, $qryBld = null) {
        // @HOOK_ORDER_MOVE
        $other = $direction == 'up'? $this->getPrevious($chCategory, $qryBld) : $this->getNext($chCategory, $qryBld);
        if(!$other) return;
        if(!$categoryProduct = $this->getCategoryProduct($chCategory)) return;
        if(!$otherCategoryProduct = $other->getCategoryProduct($chCategory)) return;
        CategoryProduct::orderList($chCategory->id, [
            $otherCategoryProduct->ord => $categoryProduct->product_id,
            $categoryProduct->ord => $otherCategoryProduct->product_id
        ]);
        // @HOOK_ORDER_MOVE_END
    }
    //END ORDERABLE

}
