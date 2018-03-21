<?php
/**
 * 华泽代付接口
 * Author: huangkaixuan
 * Date: 2018/3/21
 * Time: 15:42
 */

namespace App\Pay\Impl\TimesData;


use App\Admin\Model\UploadFile;
use App\Pay\IdConfuse;
use App\Pay\Model\Withdraw;
use App\Pay\Model\WithdrawMethod;
use App\Pay\Model\WithdrawResult;
use App\Pay\RSA;
use App\Pay\WithdrawInterface;

class Transfer implements WithdrawInterface
{
    const STATE_PENDING = 1;
    const STATE_SUCCESS = 2;
    const STATE_FAIL = 3;

    public function withdraw($withdraw_id, $amount, array $receiver_info, array $config, $notify_url)
    {
        $request = new TransferRequest($this->mixUpWithdrawId($withdraw_id), $config['mechid'], $config['url']);
        $request->setRSAInstance(new RSA(UploadFile::getFile($config['platform_public_key']), UploadFile::getFile($config['merchant_private_key']), 'base64', OPENSSL_PKCS1_PADDING, OPENSSL_ALGO_MD5));
        $request->appendData('orderId', $this->mixUpWithdrawId($withdraw_id));
        $request->appendData('money', bcmul($amount, 100));
        $request->appendData('bankCode', $receiver_info['bank_no']);

        $card = $receiver_info['bank_card'];
        $request->appendData('bankName', $card->bank->name);
        $request->appendData('bankAccount', $card->card_num);
        $request->appendData('prop', 0);
        $request->appendData('accountName', $card->holder_name);

        $response = $request->send();

        if ($response->isOk()) {
            $state = [self::STATE_FAIL => Withdraw::STATE_SEND_FAIL, self::STATE_SUCCESS => Withdraw::STATE_COMPLETE, self::STATE_PENDING => Withdraw::STATE_SUBMIT][$response->dStatus];
        } else {
            $state = Withdraw::STATE_SEND_FAIL;
        }

        if ($state === Withdraw::STATE_SUBMIT) {
            //通道处理中,发起状态轮询
            WithdrawMethod::pollState($withdraw_id);
        }

        return new WithdrawResult($state, $response, $response->getResponseNo());
    }

    public function mixUpWithdrawId($withdrawId)
    {
        return IdConfuse::mixUpId($withdrawId, 16, true);
    }

    public function receiverInfoDescription()
    {
        return [
            'bank_card' => '银行卡对象,后端设置'
        ];
    }

    public function acceptNotify(array $config)
    {
        // TODO: Implement acceptNotify() method.
    }

    public function queryState(Withdraw $withdraw, array $config)
    {
        // TODO: Implement queryState() method.
    }
}