<?php
/**
 * 单笔代付接口
 * Author: huangkaixuan
 * Date: 2017/12/19
 * Time: 17:28
 */

namespace App\Pay\Impl\MMSP;


use App\Admin\Model\UploadFile;
use App\Pay\IdConfuse;
use App\Pay\Impl\Heepay\SmallBatchTransfer;
use App\Pay\Model\Withdraw;
use App\Pay\Model\WithdrawResult;
use App\Pay\RSA;
use App\Pay\WithdrawInterface;

class SingleTransfer implements WithdrawInterface
{
    private $our_pk_path;//我方公钥
    private $their_pk_path;//通道公钥
    private $our_sk_path;//我方私钥

    public function withdraw($withdraw_id, $amount, array $receiver_info, array $config, $notify_url)
    {
        $result = new WithdrawResult();
        $card = $receiver_info['bank_card'];
        $amount *= 100;
        $encrypt = [
            'CMDID' => hexdec('0x1001'),
            'VERSION' => '1.0',
            'MERNO' => $config['MERNO'],
            'ORDERNO' => $this->mixUpWithdrawId($withdraw_id),
            'REQTIME' => date('YmdHis'),
            'AMT' => (int)$amount,
            'BUSITYPE' => '001',
            'ACCNAME' => $card->holder_name,
            'ACCNO' => $card->card_num,
            'MEMO' => '',
            'PAYTYPE' => isset($config['PAYTYPE']) ? $config['PAYTYPE'] : 1,
        ];

//        if (isset($receiver_info['BANKSETTLENO'])) {
//            $encrypt['BANKSETTLENO'] = $receiver_info['BANKSETTLENO'];
//        }

        //加密
        $RSAEncryptor = $this->getEncryptor($config);
        $encrypt = json_encode($encrypt, JSON_UNESCAPED_UNICODE);
        $sign = $RSAEncryptor->sign($encrypt);
        $encrypt = $RSAEncryptor->encrypt($encrypt);
        $data = json_encode(['ENCRYPT' => $encrypt, 'SIGN' => $sign]);

        //请求-响应
        $response = SmallBatchTransfer::send_post($config['URL'], $data, null);
        $result->raw_response = $response;

        if ($response = json_decode($response)) {
            if ($response->RETCODE === '0000') {
                //交易成功
                $result->state = Withdraw::STATE_SUBMIT;
                $encrypt = $response->ENCRYPT;
                $RSADecryptor = new RSA($this->their_pk_path);
                $response = json_decode($RSADecryptor->decrypt($encrypt));
                if ($response) {
                    $result->raw_response = json_encode($response, JSON_UNESCAPED_UNICODE);
                    $result->out_trade_no = $response->BSORDERNO;
                    switch ($response->PAYSTATE) {
                        case 1:
                            //付款成功
                            $result->state = Withdraw::STATE_COMPLETE;
                            break;
                        case 2:
                            //付款中
                            $result->state = Withdraw::STATE_SUBMIT;
                            break;
                        case 3:
                            //付款失败
                        case 4:
                            //已退汇
                            $result->state = Withdraw::STATE_PROCESS_FAIL;
                    }
                }
            }
        }

        return $result;
    }

    public function mixUpWithdrawId($withdrawId)
    {
        return IdConfuse::mixUpId($withdrawId, 15, true);
    }

    private function getEncryptor(array $config)
    {
        $this->initKeys($config);
        return new RSA($this->our_pk_path, $this->our_sk_path);
    }

    /**
     * 初始化key
     * @param array $config
     */
    private function initKeys(array $config)
    {
        if (isset($config['our_pk_path'])) {
            $this->our_pk_path = UploadFile::getFile($config['our_pk_path']);
        }

        if (isset($config['our_sk_path'])) {
            $this->our_sk_path = UploadFile::getFile($config['our_sk_path']);
        }

        if (isset($config['their_pk_path'])) {
            $this->their_pk_path = UploadFile::getFile($config['their_pk_path']);
        }
    }

    public function receiverInfoDescription()
    {
        return [
            'bank_card' => '银行卡对象,后端设置'
        ];
    }

    public function acceptNotify(array $config)
    {
        $this->initKeys($config);

        // TODO: Implement acceptNotify() method.
    }

    public function queryState(Withdraw $withdraw, array $config)
    {
        // TODO: Implement queryState() method.
    }
}