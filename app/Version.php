<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Version extends Model
{
    //
    const PLATFORM_IOS = 0;

    const PLATFORM_ANDROID = 1;

    const TYPE_DEFAULT = 0;

    const TYPE_UPGRADE = 1;

    const TYPE_FORCE_UPGRADE = 2;

    public function getUrlAttribute($value) {
        if (filter_var($value, FILTER_VALIDATE_URL)) {
            return $value;
        }
        return Storage::disk(config('admin.upload.disk'))->url($value);
    }
}
