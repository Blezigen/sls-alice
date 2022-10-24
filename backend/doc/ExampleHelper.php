<?php

namespace doc;

class ExampleHelper
{
    public static function paginator(string $class, int $count = 10)
    {
        $result = [];
        for ($i = 0; $i < $count; ++$i) {
            $result[] = $class::__makeDocumentationEntity();
        }

        return [
            'data' => $result,
            '_links' => [
                'self' => [
                    'href' => "{url}?paginator[page]=1&paginator[limit]={$count}",
                ],
                'first' => [
                    'href' => "{url}?paginator[page]=1&paginator[limit]={$count}",
                ],
                'last' => [
                    'href' => "{url}?paginator[page]=10&paginator[limit]={$count}",
                ],
                'next' => [
                    'href' => "{url}?paginator[page]=2&paginator[limit]={$count}",
                ],
            ],
            '_meta' => [
                'totalCount' => $count,
                'pageCount' => 1,
                'currentPage' => 1,
                'perPage' => 1,
            ],
        ];
    }

    public static function data(string $class)
    {
        $data = $class::__makeDocumentationEntity();

        return [
            'data' => $data,
        ];
    }
}
