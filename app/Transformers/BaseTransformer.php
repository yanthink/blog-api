<?php

namespace App\Transformers;

use Illuminate\Database\Eloquent\Collection;
use League\Fractal\TransformerAbstract;

class BaseTransformer extends TransformerAbstract
{
    protected function collectionAndEagerLoadRelations(
        Collection $collections,
        TransformerAbstract $transformer,
        $identifier,
        $resourceKey = null
    )
    {
        if ($collections->count() > 1) {
            $eagerLoads = $this->getEagerLoad($transformer, $identifier);

            $relations = [];
            foreach ($eagerLoads as $key => $relation) {
                if (!$collections[0]->relationLoaded($relation)) {
                    $relations[] = $relation;
                }
            }

            if ($relations) {
                $collections->load($relations);
            }
        }

        return $this->collection($collections, $transformer, $resourceKey);
    }

    protected function getEagerLoad(TransformerAbstract $transformer, $identifier)
    {
        $currentScope = $this->getCurrentScope();
        $fractalManager = $currentScope->getManager();

        $identifier = implode('.', array_filter([$currentScope->getIdentifier(), $identifier]));

        $requestedIncludes = $fractalManager->getRequestedIncludes();
        $excludedIncludes = $fractalManager->getRequestedExcludes();

        $includes = $transformer->getDefaultIncludes();

        foreach ($transformer->getAvailableIncludes() as $include) {
            if (in_array(implode('.', [$identifier, $include]), $requestedIncludes)) {
                $includes[] = $include;
            }
        }

        foreach ($includes as $include) {
            if (in_array(implode('.', [$identifier, $include]), $excludedIncludes)) {
                $includes = array_diff($includes, [$include]);
            }
        }

        return $includes;
    }
}