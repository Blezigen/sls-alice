<?php

namespace common\modules\filter;

class FilterMethodFactory
{
    /** @var array|AbstractFilterMethod[] */
    private array $filters = [];

    public function __construct($filters = [])
    {
        $this->filters = $filters;
    }

    public function make($filterString)
    {
        foreach ($this->filters as $filterClass) {
            $filter = $filterClass::make($filterString);
            if ($filter) {
                return $filter;
            }
        }

        return false;
    }
}
