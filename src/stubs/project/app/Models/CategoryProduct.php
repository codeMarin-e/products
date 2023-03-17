<?php

    namespace App\Models;

    use Illuminate\Database\Eloquent\Relations\Pivot;
    use App\Traits\MacroableModel;
    use App\Traits\Orderable;
    use Illuminate\Support\Facades\DB;

    class CategoryProduct extends Pivot
    {
        use MacroableModel;

        // @HOOK_TRAITS

        //ORDERABLE
        use Orderable {
            Orderable::onDeleted_orderable as bugedOnDeleted_orderable;
        }
        public function orderableQryBld($qryBld = null) {
            $qryBld = $qryBld? clone $qryBld : $this;
            return $qryBld->where([
                [ 'category_id', $this->category_id ],
            ]);
        }

        public function onDeleted_orderable($model) { //overwrite because of the bug of not showing all attributes
            $realModel = static::where([
                ['product_id', '=', $model->product_id],
                ['category_id', '=', $model->category_id]
            ])->first();
            $this->bugedOnDeleted_orderable($realModel);
        }

        public static function orderList($categoryId, $orderList = array()) {
            if(empty($orderList)) return;
            $fakeModel = static::getModel();
            $table = $fakeModel->getTable();
            $primaryKey = 'product_id';
            $orderField = static::getOrdField();
            $prepares = [];
            $statement = "UPDATE {$table} SET {$table}.{$orderField} = (CASE {$table}.{$primaryKey}";
            foreach($orderList as $newOrd => $objId) {
                $statement .= " WHEN ? THEN ?";
                $prepares[] = $objId;
                $prepares[] = $newOrd;
            }
            $objIds = array_values($orderList);
            $whereInStm = implode(', ', array_fill(0, count($objIds), '?'));
            $statement .= " END), {$table}.updated_at = ?";

            $statement .= " WHERE {$table}.{$primaryKey} IN ({$whereInStm}) AND {$table}.category_id = {$categoryId}";
            $prepares = array_merge($prepares, [ new \Datetime() ], $objIds);
            return DB::statement( DB::raw($statement)->getValue(DB::connection()->getQueryGrammar()), $prepares);
        }
        //ORDERABLE

        protected static function boot() {
            parent::boot();
            static::updating( static::class.'@onUpdating_orderable' );
            static::updated( static::class.'@onUpdated_orderable' );
        }

        public function onUpdating_orderable($model) {
            if (!$model->isDirty('category_id'))
                return;
            $model->ord = static::freeOrd($model->orderableQryBld());
        }

        public function onUpdated_orderable($model) {
            if(!$model->isDirty('category_id'))
                return;
            $model->category_id = $model->getOriginal('category_id');
            $model->ord = $model->getOriginal('ord');
            $model->onDeleted_orderable($model);
        }
    }
