<?php
/**
 * 微信公众号支付
 * Author: huangkaixuan
 * Date: 2017/12/16
 * Time: 11:37
 */

namespace App\Pay\Impl\MMSP;


use App\Pay\DepositInterface;
use App\Pay\IdConfuse;
use App\Pay\Impl\MMSP\SDK\wxscan;

class WechatOfficialAccount extends WechatH5
{

    public function deposit($deposit_id, $amount, array $config, $notify_url, $return_url)
    {
        //Log::info($config);
        $amount *= 100; //单位:分
        $outID = IdConfuse::mixUpDepositId($deposit_id, 20);
        $wxscanMod = new wxscan();
        $wxscanMod->SetCommandID(hexdec($config['CommandID']));

        $wxscanMod->SetSeqID('1');
        $wxscanMod->SetNodeType('3');
        $wxscanMod->SetNodeID('openplat');
        $wxscanMod->SetVersion('1.0.0');
        //$wxscanMod->SetAGENTNO($_POST['AGENTNO']);//不传代理商就默认是商户交易
        $wxscanMod->SetMERNO($config['MERNO']);
        $wxscanMod->SetTERMNO($config['TERMNO']);
        $wxscanMod->SetTRADETYPE($config['TRADETYPE']);
        $wxscanMod->SetIST0($config['IST0']);
        $wxscanMod->SetAMT((int)$amount);
        $wxscanMod->SetCUR('CNY');
        $wxscanMod->SetGOODSNAME(DepositInterface::GOOD_NAME);
        $wxscanMod->SetNOTIFY_URL($notify_url);
        //$wxscanMod->SetTIME_END($_POST['TIME_END']);
        $wxscanMod->SetIP(request()->getClientIp());
        //$wxscanMod->SetJUMP_URL($return_url);
        $wxscanMod->SetMERORDERID($outID);
        $wxscanMod->SetRANDSTR(str_random(20));
        $wxscanMod->SetOPENID(request()->query('openid'));
        $wxscanMod->SetSUP_APPID($config['OFFICIAL_APPID']);
        $wxscanMod->SetRAW($config['IS_RAW']);
        if ($wxscanMod->GetTRADETYPE() == '2' && $wxscanMod->IsOPENIDSet() == false) {
            return null;
        } else {
            $wxscanMod->BodyAes();
            $wxscanMod->MakeSign($config['KEY']);
            $result = $wxscanMod->send($config['URL'], $config['KEY']);
            //Log::info($wxscanMod->GetValues());
            if ($result['STATUS'] == 1) {
                //Log::info($result);
                return $result['URL'];
            } else {
                PayLogger::deposit()->error('公众号预支付错误', [$result]);
                return null;
            }

        }

    }
}