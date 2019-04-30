<?php

namespace Axn\Illuminate\Database\Eloquent;

use Illuminate\Database\Eloquent\SoftDeletes as EloquentSoftDeletes;

trait SoftDeletes
{
    use EloquentSoftDeletes;

    public function scopeWithoutTrashedExcept($query, $exceptId)
    {
        return $query
            ->withoutTrashed()
            ->orWhere($this->getKeyName(), $exceptId);
    }
}
