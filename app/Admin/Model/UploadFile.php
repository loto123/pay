<?php

namespace App\Admin\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Mockery\Exception;

class UploadFile extends Model
{
    use SoftDeletes;

    const UPDATED_AT = null;
    protected $table = 'admin_upload_file';
    protected $guarded = ['id'];
    protected $dates = ['deleted_at'];

    public static function boot()
    {
        parent::boot();

        static::deleted(function ($model) {
            Storage::disk('admin')->delete($model->save_path);
        });
    }

    /**
     * 取得上传文件
     * @param $id
     * @return mixed
     */
    public static function getFile($id)
    {
        $file = self::find($id);
        if (!$file) {
            throw new Exception('上传文件' . $id . '不存在');
        }
        return storage_path('app/admin/') . $file->save_path;
    }

    public function uploadBy()
    {
        return DB::table('admin_users')->find($this->user_id);
    }
}
