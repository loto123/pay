<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PetPart extends Model
{
    //
    public function pet() {
        return $this->belongsTo(PetType::class, "id", "pet_id");
    }

    public function items() {
        return $this->hasMany(PetPartItem::class, "pet_part_id", 'id');
    }
}
