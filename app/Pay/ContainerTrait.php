<?php

namespace App\Pay;

use App\Pay\Model\Container;

/**
 * 容器Trait
 * Author: huangkaixuan
 * Date: 2017/12/6
 * Time: 21:45
 */
trait ContainerTrait
{
    public function __call($method, $parameters)
    {
        if (method_exists(Container::class, $method)) {
            return call_user_func_array([$this->container(), $method], $parameters);
        }
        return parent::__call($method, $parameters);
    }
}