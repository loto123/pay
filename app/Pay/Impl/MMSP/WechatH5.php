<?php
/**
 * 微信H5支付(限外部浏览器)
 * Author: huangkaixuan
 * Date: 2017/12/16
 * Time: 11:34
 */

namespace App\Pay\Impl\MMSP;


use App\Pay\DepositInterface;
use App\Pay\Impl\MMSP\SDK\base;
use App\Pay\Model\Deposit;
use Illuminate\Support\Facades\Log;
use Mockery\Exception;

class WechatH5 implements DepositInterface
{


    public function deposit($deposit_id, $amount, array $config, $notify_url, $return_url)
    {
        $amount *= 100; //单位:分
        $wxh5payMod = new SDK\wxh5pay();
        $wxh5payMod->SetCommandID(hexdec('0x0911'));
        $wxh5payMod->SetSeqID('1');
        $wxh5payMod->SetNodeType('3');
        $wxh5payMod->SetNodeID('openplat');
        $wxh5payMod->SetVersion('1.0.0');
        $wxh5payMod->SetAGENTNO($config['AGENTNO']);//不传代理商就默认是商户交易
        $wxh5payMod->SetMERNO($config['MERNO']);
        $wxh5payMod->SetTERMNO($config['TERMNO']);
        $wxh5payMod->SetAMT($amount);
        $wxh5payMod->SetCUR('CNY');
        $wxh5payMod->SetGOODSNAME(DepositInterface::GOOD_NAME);
        $wxh5payMod->SetNOTIFY_URL($notify_url);
        $wxh5payMod->SetJUMP_URL($return_url . "?batch=$deposit_id");
        //$wxh5payMod->SetTIME_END($_POST['TIME_END']);
        $wxh5payMod->SetIP(request()->getClientIp());
        $wxh5payMod->SetMERORDERID($deposit_id);//订单号
        $wxh5payMod->SetRANDSTR(str_random(20));
        $wxh5payMod->SetLIMIT_PAY($config['LIMIT_PAY']);
        $wxh5payMod->BodyAes();
        $wxh5payMod->MakeSign($config['KEY']);
        $result = $wxh5payMod->send($config['URL'], $config['KEY']);
        //Log::info($wxh5payMod->GetValues());
        if ($result['STATUS'] == 1) {
            return $result['URL'];
        } else {
            Log::error($result);
            return null;
        }
    }

    public function acceptNotify(array $config)
    {
        $params = request()->post('result');
        if (!$params) {
            $params = json_decode(file_get_contents("php://input"), true);
            $resultMod = new base();
            $chkStatus = $resultMod->ckSign($params, $config['KEY']);
            if ($chkStatus) {
                if ($params['STATUS'] == 1) {
                    $deposit = Deposit::find((int)$params['MERORDERID']);
                    if ($deposit) {
                        //把支付结果更改商户自己的交易流水
                        $deposit->out_batch_no = $params['ORDERNO'];
                        $deposit->state = $params['AMT'] / 100 < $deposit->amount ? Deposit::STATE_PART_PAID : Deposit::STATE_COMPLETE;
                        echo 'SUCCESS';
                        return $deposit;
                    }
                }
            }
            return null;
        }
    }

    public function parseReturn()
    {
        $deposit_id = request()->query('batch');
        $deposit = Deposit::find($deposit_id);
        if (!$deposit) {
            throw new Exception('无效订单');
        }

        return ['out_batch_no' => $deposit->out_batch_no, 'state' => $deposit->state, 'amount' => $deposit->amount];

    }
}