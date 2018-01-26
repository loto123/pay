<?php
/**
 * 交易商交易
 * Author: huangkaixuan
 * Date: 2018/1/26
 * Time: 14:48
 */

namespace App\Admin\Model;


use App\Pay\Model\BillMatch;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;

class DealerTransaction extends Model
{
    public static function with($relations)
    {
        return new static;
    }

    public function paginate()
    {
        $perPage = Request::get('per_page', 10);

        $page = Request::get('page', 1);

        $start = ($page - 1) * $perPage;

        $buyDealState = BillMatch::STATE_DEAL_CLOSED;
        // 运行sql获取数据数组
        $sql = <<<EOT
SELECT SQL_CALC_FOUND_ROWS 
  IFNULL(
    `from_user_tmp`.`avatar`,
    '/images/personal.jpg'
  ) AS `from_avatar`,
  `from_user_tmp`.`name` AS `from_name`,
  `from_user_tmp`.`mobile` AS `from_uid`,
  to_uid,
  IFNULL(
    `to_user_tmp`.`avatar`,
    '/images/personal.jpg'
  ) AS `to_avatar`,
  IFNULL(
    `to_user_tmp`.`name`,
    '公司号'
  ) AS `to_name`,
  `to_user_tmp`.`mobile` AS `to_uid`,
  create_time,
  price,
  pet_id,
  trade_type 
FROM
  (#交易商买入
  SELECT 
    `user_id` AS `from_uid`,
    `pay_bill_match`.`created_at` AS `create_time`,
    `pay_sell_bill`.`place_by` AS `to_uid`,
    `price`,
    `pet_id`,
    '购入' AS `trade_type` 
  FROM
    `pay_bill_match` 
    JOIN `pay_sell_bill` 
      ON `pay_bill_match`.`by_dealer` = 1 
      AND `pay_bill_match`.`state` = $buyDealState 
      AND `sell_bill_id` = `pay_sell_bill`.`id` 
  UNION
  ALL #交易商卖出
  SELECT 
    `place_by`,
    `created_at`,
    `belong_to`,
    `price`,
    `pet_id`,
    '售出' 
  FROM
    `pay_sell_bill` 
  WHERE `by_dealer` = 1 
    AND `deal_closed` = 1 
  UNION
  ALL #交易商进货
  SELECT 
    `dealer_id`,
    `created_at`,
    0,
    `price`,
    `pet_id`,
    '购入' 
  FROM
    `pay_dealer_pets_stock`) `dealer_transactions` 
  JOIN `users` AS `from_user_tmp` 
    ON `dealer_transactions`.`from_uid` = `from_user_tmp`.`id` 
  JOIN `users` AS `to_user_tmp` 
    ON `dealer_transactions`.`to_uid` = `to_user_tmp`.`id` 
ORDER BY `create_time` DESC limit $start,$perPage
EOT;


        $result = DB::select($sql);

        $movies = static::hydrate($result);

        $total = DB::select('select FOUND_ROWS() as total')[0]->total;
        $paginator = new LengthAwarePaginator($movies, $total, $perPage);

        $paginator->setPath(url()->current());

        return $paginator;
    }
}