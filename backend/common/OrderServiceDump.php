<?php

namespace common;

use common\contracts\IOrderService;

class OrderServiceDump implements IOrderService
{
    public function getReservesByTourIds($tourIds)
    {
        return [];
    }
}
