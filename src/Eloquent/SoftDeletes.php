<?php

namespace Axn\Illuminate\Database\Eloquent;

use Illuminate\Database\Eloquent\SoftDeletes as EloquentSoftDeletes;

trait SoftDeletes
{
    use EloquentSoftDeletes;

    public function scopeWithoutTrashedExcept($query, $exceptId = null)
    {
        // Replace :
        //  $query->where(function($query) use ($exceptId){
        //
        // By :
        //  $query->where(function() use ($query, $exceptId){
        //
        // If we do not do that the builder loses his scopes...

        $query->where(function() use ($query, $exceptId){
            $query->withoutTrashed();
            $query->when($exceptId, function($query, $exceptId){
                if (is_array($exceptId)) {
                    $query->orWhereIn($this->getQualifiedKeyName(), $exceptId);
                } else {
                    $query->orWhere($this->getQualifiedKeyName(), $exceptId);
                }
            });
        });
    }
}
