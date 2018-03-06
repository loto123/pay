<?php

namespace App\Channels;

use App\SystemMessage;
use App\User;
use Illuminate\Notifications\Notification;
use JPush\Client as JPush;
use Log;

class JPushChannel
{

    /**
     * @var JPush
     */
    private $jpush;


    public function __construct()
    {
    }

    public function send($notifiable, Notification $notification)
    {
        $DatabaseNotification = $notifiable->notifications->first();
        if (!$DatabaseNotification) {
            return;
        }
        $app_key = config('jpush.app_key');
        $master_secret = config('jpush.secret');
//        Log::info($DatabaseNotification->uid);
//        Log::info($notification->uid);
        Log::info("jpush");
        $user = User::find($DatabaseNotification->notifiable_id);

        /* @var $user User */
        try {
            $data = $DatabaseNotification->data;
//            $message = SystemMessage::find($data['param']['message_id']);
            /* @var $message \App\SystemMessage */
            $jpush = (new JPush($app_key, $master_secret))->push();
            $response = $jpush
                ->setPlatform('all')
                ->addAlias($user->en_id())
                ->setNotificationAlert($data['title'])
                ->iosNotification($data['content'], array(
//                'sound' => 'sound.caf',
                    'badge' => $user->unreadNotifications()->count(),
                    // 'content-available' => true,
                    // 'mutable-content' => true,
                    'extras' => array(
                        'link' => $data['param']['link'],
                        'type' => (int)$data['type']
                    ),
                ))
                ->androidNotification($data['content'], array(
                    'title' => $data['title'],
                    // 'builder_id' => 2,
                    'extras' => array(
                        'link' => $data['param']['link'],
                        'type' => (int)$data['type']
                    ),
                ))->options(array(
                    // sendno: 表示推送序号，纯粹用来作为 API 调用标识，
                    // API 返回时被原样返回，以方便 API 调用方匹配请求与返回
                    // 这里设置为 100 仅作为示例
                    // 'sendno' => 100,
                    // time_to_live: 表示离线消息保留时长(秒)，
                    // 推送当前用户不在线时，为该用户保留多长时间的离线消息，以便其上线时再次推送。
                    // 默认 86400 （1 天），最长 10 天。设置为 0 表示不保留离线消息，只有推送当前在线的用户可以收到
                    // 这里设置为 1 仅作为示例
                    // 'time_to_live' => 1,
                    // apns_production: 表示APNs是否生产环境，
                    // True 表示推送生产环境，False 表示要推送开发环境；如果不指定则默认为推送生产环境
                    'apns_production' => config('app.debug') ? false : true,
                    // big_push_duration: 表示定速推送时长(分钟)，又名缓慢推送，把原本尽可能快的推送速度，降低下来，
                    // 给定的 n 分钟内，均匀地向这次推送的目标用户推送。最大值为1400.未设置则不是定速推送
                    // 这里设置为 1 仅作为示例
                    // 'big_push_duration' => 1
                ))->send();
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