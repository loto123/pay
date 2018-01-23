<?php

namespace App\Jobs;

use App\PetType;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Storage;

class Pet implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $pet;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(\App\Pet $pet)
    {
        //
        $this->pet = $pet;
        $this->queue = 'pets';
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        //
        $pet_type = PetType::inRandomOrder()->first();
        /* @var $pet_type \App\PetType */
        $prefix = Storage::disk(config('admin.upload.disk'))->getAdapter()->getPathPrefix();
        if (!file_exists(!$prefix.$pet_type->image)) {
            return;
        }
        $pathinfo = pathinfo($prefix.$pet_type->image);
        $extension = strtolower($pathinfo['extension']);
        if ($extension == 'jpg') {
            $template = imagecreatefromjpeg($prefix.$pet_type->image);
            $method = "imagejpeg";
            $quality = 100;
        } else if ($extension == 'png') {
            $template = imagecreatefrompng($prefix.$pet_type->image);
            $method = "imagepng";
            $quality = 0;
        } else {
            return;
        }
        $parts = [];
        foreach ($pet_type->parts()->orderBy("z_index")->get() as $_part) {
            /* @var $_part \App\PetPart */
            $_item = $_part->items()->inRandomOrder()->first();
            /* @var $_item \App\PetPartItem */

            if ($_item) {
                if (!file_exists($prefix.$_item->image)) {
                    continue;
                }
                $_pathinfo = pathinfo($prefix.$_item->image);
                $_extension = strtolower($_pathinfo['extension']);
                if ($_extension == 'jpg') {
                    $part_image = imagecreatefromjpeg($prefix.$_item->image);
                } else if ($_extension == 'png') {
                    $part_image = imagecreatefrompng($prefix.$_item->image);
                } else {
                    continue;
                }
                $parts[$_part->id] = $_item->id;
                $size = getimagesize($prefix.$_item->image);
                imagecopy($template, $part_image, $_part->x_index, $_part->y_index, 0, 0, $size[0], $size[1]);
            }

        }
        $hash[$pet_type->id] = $parts;
        $hash_str = serialize($hash);
        $exist = \App\Pet::where("hash", $hash_str)->first();
        if ($exist) {
            throw new \Exception();
        }
        $this->pet->hash = $hash_str;
        $this->pet->save();
//        var_dump($template);

        ob_start();
        $method($template, null, $quality);
        $content = ob_get_clean();
        $path = 'pet/'.md5($hash_str).'.'.$extension;
        Storage::disk('public')->put($path, $content);
//        $method($template, "/tmp/test.png", 100);
//        file_put_contents("/tmp/test.png", ob_get_clean());
        $this->pet->image = $path;
        $this->pet->status = \App\Pet::STATUS_HATCHED;
        $this->pet->save();

        var_dump($path);
    }
}
