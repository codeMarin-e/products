<?php
    namespace App\Traits;

    use App\Models\CategoryProduct;
    use App\Models\Product;

    trait CategoryProductTrait {

        public static function bootCategoryProductTrait() {
            static::deleting( static::class.'@onDeleting_products' );
        }

        public function products() {
            return $this->belongsToMany(Product::class, 'category_product', 'category_id', 'product_id')
                ->using(CategoryProduct::class)
                ->withPivot('ord')
                ->orderByPivot('ord')
                ->withTimestamps();
        }

        public function onDeleting_products($category) {
            $products = $category->products()->get();
            foreach($products as $product) {
                if($product->categories()->count() > 1) continue;
                $product->delete();
            }
            $category->products()->detach();
        }
    }
