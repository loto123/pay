<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * Class Pet
 * @property integer $user_id
 * @package App
 */
class Pet extends Model
{
    use Skip32Trait;

    protected static $skip32_id = '070bd01ce7d3161920f6';

    const DEALER_ROLE_NAME = 'pet_dealer';//宠物交易商角色名

    const TYPE_EGG = 0;

    const TYPE_PET = 1;

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
        if (!filter_var($value, FILTER_VALIDATE_URL) === false) {
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
        if ($user_id == $this->user_id) {
            return false;
        }
        $from_user_id = $this->user_id;
        $this->user_id = $user_id;
        $this->status = self::STATUS_HATCHED;
        $record = new PetRecord();
        $record->pet_id = $this->id;
        $record->from_user_id = $from_user_id;
        $record->to_user_id = $user_id;
        $record->type = PetRecord::TYPE_TRANSFER;

        DB::beginTransaction();
        try {
            $this->save();
            $record->save();
        } catch (\Exception $e){
            DB::rollBack();
            return false;
        }
        DB::commit();
        return true;
    }

    /**
     * 宠物可售状态
     * @return bool
     */
    public function for_sale() {
        return $this->status == self::STATUS_HATCHED;
    }

    /**
     * 孵蛋
     * @return bool
     */
    public function hatch() {
        if ($this->status != Pet::STATUS_UNHATCHED) {
            return false;
        }
        $this->status = Pet::STATUS_HATCHING;
        $this->save();
        \App\Jobs\Pet::dispatch($this);
        return true;
    }
}
