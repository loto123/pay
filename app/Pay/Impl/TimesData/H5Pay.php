<?php
/**
 * 微信/支付宝H5/H5快捷支付
 * Author: huangkaixuan
 * Date: 2018/3/9
 * Time: 16:59
 */

namespace App\Pay\Impl\TimesData;


use App\Admin\Model\UploadFile;
use App\Pay\DepositInterface;
use App\Pay\IdConfuse;
use App\Pay\Model\Deposit;
use App\Pay\Model\DepositMethod;
use App\Pay\PayLogger;
use App\Pay\RSA;

class H5Pay implements DepositInterface
{

    public function deposit($deposit_id, $amount, array $config, $notify_url, $return_url, $timeout)
    {
        $request = new PayRequest('h5_pay_request', $this->mixUpDepositId($deposit_id), $config['mechid'], '01');
        $request->setNotifyUrl($notify_url);
        $request->setRSAInstance(new RSA(UploadFile::getFile($config['platform_public_key']), UploadFile::getFile($config['merchant_private_key']), 'base64', OPENSSL_PKCS1_PADDING, OPENSSL_ALGO_MD5));
        $request->appendData('paytype', 0);
        $request->appendData('total_fee', bcmul($amount, 100));
        $request->appendData('device_ip', request()->ip());

        $response = $request->send();
        if ($response->isOk()) {
            return $response->pay_url;
        } else {
            PayLogger::deposit()->error('华泽支付H5下单错误', ['code' => $response->getCode(), 'msg' => $response->getMessage()]);
        }
    }

    public function mixUpDepositId($depositId)
    {
        return 'p' . IdConfuse::mixUpId($depositId, 31, true);
    }

    public function acceptNotify(array $config)
    {
        $response = file_get_contents('php://input');
        $response = Request::parseResponse($response, Message::SIGN_RSA1, new RSA(UploadFile::getFile($config['platform_public_key']), UploadFile::getFile($config['merchant_private_key']), 'base64', OPENSSL_PKCS1_PADDING, OPENSSL_ALGO_MD5));
        $deposit_id = IdConfuse::recoveryId(substr($response->getReqNo(), 1), true);
        if ($response->isOk()) {
            if ($response->tType == 0) {
                $deposit = Deposit::find($deposit_id);
                if ($deposit) {
                    $deposit->out_batch_no = $response->tNo;
                    $deposit->state = bcdiv($response->allFee, 100) < $deposit->amount ? Deposit::STATE_PART_PAID : Deposit::STATE_COMPLETE;
                    return $deposit;
                }
            }
            echo '0000';
        } else {
            throw new \Exception('支付失败', ['code' => $response->getCode(), 'msg' => $response->getMessage(), 'order' => $deposit_id]);
        }

    }

    public function parseReturn(DepositMethod $method)
    {
        // TODO: Implement parseReturn() method.
    }

    public function benefitShare(array $config, Deposit $deposit)
    {
        return true;
    }
}