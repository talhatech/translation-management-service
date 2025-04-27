<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

abstract class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    protected function getPaginate(Request $request, Relation|Builder $query): LengthAwarePaginator
    {
        if ($query instanceof Relation) {
            // If $query is a relationship, get the underlying query builder
            $query = $query->getQuery();
        }
        return $query->paginate(
            $request->has('per_page') ?
                $request->input('per_page') :
                config('constants.global.record_per_page')
        );
    }
}
