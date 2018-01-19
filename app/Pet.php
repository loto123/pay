<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Pet extends Model
{
    //
    public function parts() {
        return $this->hasMany(PetPart::class, 'pet_id', "id");
    }
}
