<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PetPartItem extends Model
{
    //
    public function pet_part() {
        return $this->belongsTo(PetPart::class, "id", "pet_part_id");
    }
}
