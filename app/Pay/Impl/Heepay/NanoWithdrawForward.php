<?php
/**
 * 汇付宝提现申请公司内部转发
 * Author: huangkaixuan
 * Date: 2018/3/2
 * Time: 14:56
 */

namespace App\Pay\Impl\Heepay;


use App\Pay\IdConfuse;
use App\Pay\Model\Withdraw;
use App\Pay\Model\WithdrawResult;
use App\Pay\PayLogger;
use App\Pay\WithdrawInterface;

class NanoWithdrawForward implements WithdrawInterface
{

    public function withdraw($withdraw_id, $amount, array $receiver_info, array $config, $notify_url)
    {
        $result = new WithdrawResult();
        $card = $receiver_info['bank_card'];

        do {
            //网银提现
            $batch_no = $this->mixUpWithdrawId($withdraw_id);

            $params = [
                'order_no' => $batch_no,
                'batch_amt' => $amount,
                'bank_no' => $receiver_info['bank_no'],
                'bank_card_num' => $card->card_num,
                'id_card_name' => $card->holder_name,
                'to_who' => '0',
                'province' => $card->province,
                'city' => $card->city,
                'subbranch' => $card->bank->name,
                'user_id' => $withdraw_id,
                'channel' => $config['nano_channel'],
            ];
            PayLogger::withdraw()->debug('汇付宝提现公司接口', $params);
            $res_json = SmallBatchTransfer::send_post($config['url'], http_build_query($params), null);
            PayLogger::withdraw()->debug('提现返回', ['json' => $res_json]);
            $result->raw_response = $res_json;
            $response = json_decode($res_json);
            if (empty($response)) {
                if ($response === false) {
                    PayLogger::withdraw()->error('json解析错误!', [json_last_error_msg()]);
                } else {
                    PayLogger::withdraw()->error('汇付宝提现转发接口返回空');
                }
                break;
            }

            if ($response->code == 1) {
                $result->state = Withdraw::STATE_SUBMIT;
            }
        } while (false);

        return $result;
    }

    public function mixUpWithdrawId($withdrawId)
    {
        return IdConfuse::mixUpId($withdrawId, 15);
    }

    public function receiverInfoDescription()
    {
        return [
            'bank_card' => '银行卡对象,后端设置'
        ];
    }

    public function acceptNotify(array $config)
    {
        //来源判断
        if (array_key_exists('notify_whitelist', $config)) {
            $source_ip = request()->ip();
            if (!in_array(request()->ip(), explode(',', $config['notify_whitelist']))) {
                PayLogger::withdraw()->info('非法通知源IP', ['ip' => $source_ip, 'params' => request()->all()]);
            }
        } else {
            PayLogger::withdraw()->error('汇付宝提现内部接口未设置通知白名单');
            return null;
        }
        $params = request()->only('state', 'user_id');

        $withdraw = Withdraw::where([['id', $params['user_id']], ['state', Withdraw::STATE_SUBMIT]])->lockForUpdate()->first();
        if (!$withdraw) {
            return null;
        }

        $withdraw->state = $params['state'] == 1 ? Withdraw::STATE_COMPLETE : Withdraw::STATE_PROCESS_FAIL;
        return $withdraw;
    }
}