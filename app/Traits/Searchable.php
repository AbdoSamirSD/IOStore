<?php
namespace App\Traits;

trait Searchable
{

    public function search($query, array $searchableFields)
    {
        if (request()->has('search')) {
            return $query->where(function ($query) use ($searchableFields) {
                foreach ($searchableFields as $field) {
                    $query->orWhere($field, 'like', '%' . request('search') . '%');
                }
            });
        }
        return;
    }
}