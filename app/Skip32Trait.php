<?php

namespace App;

use App\Pay\Model\Channel;
use App\Pay\Model\MasterContainer;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Skip32;
use Zizaco\Entrust\Traits\EntrustUserTrait;

trait Skip32Trait {

    public function en_id()
    {
        return Skip32::encrypt(self::$skip32_id, $this->id);
    }

    public static function findByEnId($en_id)
    {
        return self::find(Skip32::decrypt(self::$skip32_id, $en_id));
    }
}
