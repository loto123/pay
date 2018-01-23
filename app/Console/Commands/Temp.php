<?php

namespace App\Console\Commands;

use App\PetType;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class Temp extends Command
{
    private $content_cache = [];
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'temp';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'temp';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        //
        $pet_type = PetType::find(1);
//        $path = "/Users/noname/Downloads/pets/";
        $path = "/tmp/pets/";

        foreach ($pet_type->parts as $part) {
            var_dump(file_exists($path.$part->name));
            foreach ($part->items as $item) {
                $filename = $path.$part->name.DIRECTORY_SEPARATOR.$item->name.".png";
                if (file_exists($filename)) {
                    $content = file_get_contents($filename);
                    $path = sprintf("images/%s.png", md5($item->id.time()));
                    Storage::disk(config('admin.upload.disk'))->put($path, $content);
                    $item->image = $path;
                    $item->save();
                }

            }
        }
    }
}
