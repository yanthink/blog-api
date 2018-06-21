<?php

namespace App\Serializers;

use League\Fractal\Serializer\ArraySerializer;
use League\Fractal\Pagination\PaginatorInterface;

class DataSerializer extends ArraySerializer
{

    public function collection($resourceKey, array $data)
    {
        return compact('data');
    }

    public function item($resourceKey, array $data)
    {
        return compact('data');
    }

    public function meta(array $meta)
    {
        return $meta;
    }

    public function paginator(PaginatorInterface $paginator)
    {
        $pagination = [
            'total' => (int)$paginator->getTotal(),
            'current' => (int)$paginator->getCurrentPage(),
            'pageSize' => (int)$paginator->getPerPage(),
        ];

        return compact('pagination');
    }

    public function mergeIncludes($transformedData, $includedData)
    {
        if (!$this->sideloadIncludes()) {
            foreach ($includedData as $identifier => $data) {
                $key = snake_case($identifier);
                $includedData[$key] = current($data);
                if ($key != $identifier) {
                    unset($includedData[$identifier]);
                }
            }
            $transformedData = array_merge($transformedData, $includedData);
        }
        return $transformedData;
    }

}
