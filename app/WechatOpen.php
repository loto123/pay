<?php

namespace App;

use GuzzleHttp\Client;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class WechatOpen extends Model
{
    private $app_id;

    private $secret;

    private $base_url = "https://api.weixin.qq.com";

    //
    public function __construct($app_id, $secret) {
        $this->app_id = $app_id;
        $this->secret = $secret;
    }

    public function user($code) {
        $auth_info = $this->auth($code);
        Log::info("auth_info:".var_export($auth_info, true));
        if ($auth_info && isset($auth_info['openid'])) {
            $user_info = $this->userinfo($auth_info['openid'], $auth_info['access_token']);
            Log::info("user_info:".var_export($user_info, true));
            return $user_info;
        } else {
            return false;
        }
    }

    private function userinfo($openid, $access_token) {
        $client = new Client([
            // Base URI is used with relative requests
            'base_uri' => $this->base_url,
            // You can set any number of default request options.
            'timeout'  => 2.0,
        ]);
        $response = $client->get("sns/userinfo",[
            'query' => [
                'access_token' => $access_token,
                'openid' => $openid,
            ]
        ]);
        if ($response->getStatusCode() == 200) {
            return json_decode($response->getBody(), true);
        } else {
            return false;
        }
    }

    private function auth($code) {
        $client = new Client([
            // Base URI is used with relative requests
            'base_uri' => $this->base_url,
            // You can set any number of default request options.
            'timeout'  => 2.0,
        ]);
        $response = $client->get("sns/oauth2/access_token",[
            'query' => [
                'appid' => $this->app_id,
                'secret' => $this->secret,
                'code' => $code,
                'grant_type' => "authorization_code",
            ]
        ]);
        if ($response->getStatusCode() == 200) {
            return json_decode($response->getBody(), true);
        } else {
            return false;
        }
    }
}
