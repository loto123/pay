<?php
/**
 * 微信H5支付(限外部浏览器)
 * Author: huangkaixuan
 * Date: 2017/12/16
 * Time: 11:34
 */

namespace App\Pay\Impl\MMSP;


use App\Pay\DepositInterface;
use App\Pay\IdConfuse;
use App\Pay\Impl\MMSP\SDK\base;
use App\Pay\Model\Deposit;
use App\Pay\Model\DepositMethod;
use App\Pay\Model\DepositResult;
use App\Pay\PayLogger;
use Mockery\Exception;

class WechatH5 implements DepositInterface
{


    public function deposit($deposit_id, $amount, array $config, $notify_url, $return_url)
    {
        $outID = $this->mixUpDepositId($deposit_id);
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
        $wxh5payMod->SetAMT((int)$amount);
        $wxh5payMod->SetCUR('CNY');
        $wxh5payMod->SetGOODSNAME(DepositInterface::GOOD_NAME);
        $wxh5payMod->SetNOTIFY_URL($notify_url);
        $wxh5payMod->SetJUMP_URL($return_url . "?batch=$outID");
        //$wxh5payMod->SetTIME_END($_POST['TIME_END']);
        $wxh5payMod->SetIP(request()->getClientIp());
        $wxh5payMod->SetMERORDERID($outID);//订单号
        $wxh5payMod->SetRANDSTR(str_random(20));
        $wxh5payMod->SetLIMIT_PAY($config['LIMIT_PAY']);
        $wxh5payMod->BodyAes();
        $wxh5payMod->MakeSign($config['KEY']);
        $result = $wxh5payMod->send($config['URL'], $config['KEY']);
        PayLogger::deposit()->debug('充值数据', [$wxh5payMod->GetValues()]);
        if ($result['STATUS'] == 1) {
            return $result['URL'];
        } else {
            PayLogger::deposit()->error('微信H5预支付错误', [$result]);
            return null;
        }
    }

    public function mixUpDepositId($depositId)
    {
        return IdConfuse::mixUpId($depositId, 20);
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
                    $deposit = Deposit::find(IdConfuse::recoveryId($params['MERORDERID']));
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

    public function parseReturn(DepositMethod $method)
    {
        $outID = request()->query('batch');
        $deposit = $method->deposits()->where('id', IdConfuse::recoveryId($outID))->first();
        if (!$deposit) {
            throw new Exception('无效订单');
        }

        return new DepositResult($deposit->state, $deposit->amount, $deposit->out_batch_no);
    }
}