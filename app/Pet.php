<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Pet extends Model
{
    //
    public function user() {
        return $this->belongsTo(User::class, "user_id", "id");
    }

    public function pet_type() {
        return $this->belongsTo(PetType::class, "type_id", "id");
    }
}
