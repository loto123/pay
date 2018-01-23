<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Pet extends Model
{
    const STATUS_UNHATCHED = 0;

    const STATUS_HATCHING = 1;

    const STATUS_HATCHED = 2;

    const STATUS_LOCKED = 3;

    const STATUS_DELETED = 4;

    //
    public function user() {
        return $this->belongsTo(User::class, "user_id", "id");
    }

    public function pet_type() {
        return $this->belongsTo(PetType::class, "type_id", "id");
    }

    public function getImageAttribute($value) {
        if (filter_var($value, FILTER_VALIDATE_URL) === TRUE) {
            return $value;
        }
        return $value ? Storage::disk('public')->url($value) : asset("images/personal.jpg");
    }

    /**
     *  宠物转移
     * @param $user_id
     * @return bool
     */
    public function transfer($user_id) {
        return true;
    }

    /**
     * 宠物可售状态
     * @return bool
     */
    public function for_sale() {
        return true;
    }
}
