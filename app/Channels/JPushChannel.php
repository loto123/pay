<?php

namespace App\Channels;

use Log;
use Illuminate\Notifications\Notification;
use JPush\Client as JPush;

class JPushChannel
{

    /**
     * @var JPush
     */
    private $jpush;


    public function __construct()
    {
        $app_key = config('jpush.app_key');
        $master_secret = config('jpush.secret');

        $this->jpush = (new JPush($app_key, $master_secret))->push();
    }

    public function jpushEncode($str)
    {
        $find = array('-');
        $replace = array('_');
        return str_replace($find, $replace, $str);
    }

    public function send($notifiable, Notification $notification)
    {
        $message = $notification->toApp($notifiable);
        Log::info("jpush");
        $push_payload = $this->jpush
            ->setPlatform('all')
            ->addAllAudience()
            ->setNotificationAlert('Hi, JPush');
        try {
            $response = $push_payload->send();
        } catch (\JPush\Exceptions\APIConnectionException $e) {
            // try something here
            Log::info($e);
        } catch (\JPush\Exceptions\APIRequestException $e) {
            // try something here
            Log::info($e);
        } catch (\Exception $e) {
            Log::info($e);
        }
    }
}