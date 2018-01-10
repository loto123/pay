<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Version extends Model
{
    //
    const PLATFORM_IOS = 0;

    const PLATFORM_ANDROID = 1;

    const TYPE_DEFAULT = 0;

    const TYPE_UPGRADE = 1;

    const TYPE_FORCE_UPGRADE = 2;
}
