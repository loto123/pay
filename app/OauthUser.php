<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class OauthUser extends Model
{
    //
    use Skip32Trait;

    protected static $skip32_id = '0123456789abcdef0123';
}
