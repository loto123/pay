<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PetType extends Model
{
    //
    public function parts() {
        return $this->hasMany(PetPart::class, 'pet_id', "id");
    }
}
