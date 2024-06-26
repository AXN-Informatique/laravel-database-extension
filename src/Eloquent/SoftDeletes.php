<?php

namespace Axn\Illuminate\Database\Eloquent;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes as EloquentSoftDeletes;

trait SoftDeletes
{
    use EloquentSoftDeletes;

    /**
     * Like "withoutTrashed()" but with the ability of excepting some records
     * (these records will be retrieved even if they are trashed).
     *
     * @param  int|array[int]|null $exceptId
     */
    public function scopeWithoutTrashedExcept(Builder $query, $exceptId = null): void
    {
        // Replaced :
        //   $query->where(function ($query) use ($exceptId) {
        // By :
        //   $query->where(function () use ($query, $exceptId) {
        //
        // If we do not do that, the builder loses his scopes...

        $query->where(function () use ($query, $exceptId): void {
            $query
                ->withoutTrashed()
                ->when($exceptId, function ($query, $exceptId): void {
                    if (\is_array($exceptId)) {
                        $query->orWhereIn($this->getQualifiedKeyName(), $exceptId);
                    } else {
                        $query->orWhere($this->getQualifiedKeyName(), $exceptId);
                    }
                });
        });
    }
}
