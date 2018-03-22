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
use App\Pay\Model\WithdrawException;
use App\Pay\Model\WithdrawMethod;
use App\Pay\Model\WithdrawResult;
use App\Pay\RSA;
use App\Pay\WithdrawInterface;

class Transfer implements WithdrawInterface
{
    const STATE_PENDING = 1;
    const STATE_SUCCESS = 2;
    const STATE_FAIL = 3;

    private $orderId;

    public function withdraw($withdraw_id, $amount, array $receiver_info, array $config, $notify_url)
    {
        $reqNo = $this->getRequestNo($withdraw_id);
        $request = new TransferQueryRequest($reqNo, $config['mechid'], $config['url']);
        $request->setRSAInstance(new RSA(UploadFile::getFile($config['platform_public_key']), UploadFile::getFile($config['merchant_private_key']), 'base64', OPENSSL_PKCS1_PADDING, OPENSSL_ALGO_MD5));
        $request->appendData('orderId', $this->orderId);
        $request->appendData('money', bcmul($amount, 100));
        $request->appendData('bankCode', $receiver_info['bank_no']);

        $card = $receiver_info['bank_card'];
        $request->appendData('bankName', $card->bank->name);
        $request->appendData('bankAccount', $card->card_num);
        $request->appendData('prop', 0);
        $request->appendData('accountName', $card->holder_name);

        $response = $request->send();

        if ($response->isOk()) {
            $state = $this->parseWithdrawState($response->dStatus);
        } else {
            $state = Withdraw::STATE_SEND_FAIL;
        }

        if ($state === Withdraw::STATE_SUBMIT) {
            //通道处理中,轮询结果
            WithdrawMethod::pollState($withdraw_id, $config);
        }

        return new WithdrawResult($state, $response, $response->getResponseNo());
    }

    /**
     * 取reqNo参数
     * @param $withdraw_id
     * @return string
     */
    private function getRequestNo($withdraw_id)
    {
        if (!$this->orderId) {
            $this->getOrderId($withdraw_id);
        }

        return 't' . $this->orderId . WithdrawException::where('withdraw_id', $withdraw_id)->count();
    }

    /**
     * 取orderId参数
     * @param $withdraw_id
     */
    private function getOrderId($withdraw_id)
    {
        $this->orderId = $this->mixUpWithdrawId($withdraw_id);
    }

    public function mixUpWithdrawId($withdrawId)
    {
        return IdConfuse::mixUpId($withdrawId, 16, true);
    }

    private function parseWithdrawState($int)
    {
        return [self::STATE_FAIL => Withdraw::STATE_SEND_FAIL, self::STATE_SUCCESS => Withdraw::STATE_COMPLETE, self::STATE_PENDING => Withdraw::STATE_SUBMIT][$int];
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
        $orderId = $this->getOrderId($withdraw->getKey());
        $reqNo = $this->getRequestNo($withdraw->getKey());
        $request = new TransferQueryRequest($reqNo, $config['mechid'], $config['url']);
        $request->setRSAInstance(new RSA(UploadFile::getFile($config['platform_public_key']), UploadFile::getFile($config['merchant_private_key']), 'base64', OPENSSL_PKCS1_PADDING, OPENSSL_ALGO_MD5));
        $request->appendData('serialNum', $reqNo);
        $request->appendData('orderId', $orderId);

        $response = $request->send();
        if ($response->isOk()) {
            $withdraw->state = $this->parseWithdrawState($response->dStatus);
            $withdraw->out_batch_no = $reqNo;
            return new WithdrawResult($withdraw->state, $response, $response->getResponseNo());
        } else {
            throw new \Exception($response, $response->getCode());
        }
    }
}