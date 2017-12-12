<?php
/**
 * 主容器工厂
 *
 * @transaction safe
 * Author: huangkaixuan
 * Date: 2017/12/7
 * Time: 16:07
 */

namespace App\Pay\Model;


class MasterContainerFactory
{
    /**
     * 取得或生成一个主容器
     * @param null $id
     * @return MasterContainer|null
     */
    public static function get($id = null)
    {
        if ($id !== null) {
            return MasterContainer::findOrFail($id);
        } else {
            //生成
            return new MasterContainer();
        }
    }
}