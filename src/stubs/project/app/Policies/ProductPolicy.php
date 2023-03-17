<?php

namespace App\Policies;

use App\Models\Product;
use App\Models\User;

class ProductPolicy
{
    public function before(User $user, $ability) {
        // @HOOK_POLICY_BEFORE
        if($user->hasRole('Super Admin', 'admin') )
            return true;
    }

    public function view(User $user) {
        // @HOOK_POLICY_VIEW
        return $user->hasPermissionTo('products.view', request()->whereIam());
    }

    public function create(User $user) {
        // @HOOK_POLICY_CREATE
        return $user->hasPermissionTo('product.create', request()->whereIam());
    }

    public function update(User $user, Product $chProduct) {
        // @HOOK_POLICY_UPDATE
        if( !$user->hasPermissionTo('product.update', request()->whereIam()) )
            return false;
        return true;
    }

    public function delete(User $user, Product $chProduct) {
        // @HOOK_POLICY_DELETE
        if( !$user->hasPermissionTo('product.delete', request()->whereIam()) )
            return false;
        return true;
    }

    // @HOOK_POLICY_END
}
