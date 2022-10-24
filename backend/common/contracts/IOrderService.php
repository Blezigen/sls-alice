<?php

namespace common\contracts;

interface IOrderService
{
    public function getReservesByTourIds($tourIds);
}
