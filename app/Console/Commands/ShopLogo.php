<?php

namespace App\Console\Commands;

use App\Shop;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ShopLogo extends Command
{
    private $content_cache = [];
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'shop:logo {--id=*}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '更新店铺logo';

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
        $shop_ids = $this->option('id');
        if ($shop_ids) {
            if (!is_array($shop_ids)) {
                $shop_ids = [$shop_ids];
            }
            $shops = Shop::find($shop_ids);
        } else {
            $shops = Shop::all();
        }
        Log::info("update shop logo begin:".var_export($shop_ids, true));
        foreach ($shops as $shop) {
            /* @var $shop Shop */
            $this->info("shop logo:".$shop->id);
            $pic_list = [];
            foreach ($shop->users()->limit(9)->get() as $_user) {
                $pic_list[] = $_user->avatar;
            }
            if ($pic_list) {
                $path = 'logo/'.md5(sprintf("%d_%d", $shop->id, time())).'.jpg';
                Storage::disk('public')->put($path, $this->logo($pic_list));
                $this->info($path);
                $shop->logo = 'storage/'.$path;
                $shop->save();
            }
        }
        Log::info("update shop logo end:");
    }

    private function logo($pic_list) {
        $pic_list    = array_slice($pic_list, 0, 9); // 只操作前9个图片

        $bg_w    = 200; // 背景图片宽度
        $bg_h    = 200; // 背景图片高度

        $background = imagecreatetruecolor($bg_w,$bg_h); // 背景图片
        $color   = imagecolorallocate($background, 255, 250, 250); // 为真彩色画布创建白色背景，再设置为透明
        imagefill($background, 0, 0, $color);
        imageColorTransparent($background, $color);

        $pic_count  = count($pic_list);
        $lineArr    = array();  // 需要换行的位置
        $space_x    = 3;
        $space_y    = 3;
        $line_x  = 0;
        switch($pic_count) {
            case 1: // 正中间
                $start_x    = intval($bg_w/4);  // 开始位置X
                $start_y    = intval($bg_h/4);  // 开始位置Y
                $pic_w   = intval($bg_w/2); // 宽度
                $pic_h   = intval($bg_h/2); // 高度
                break;
            case 2: // 中间位置并排
                $start_x    = 2;
                $start_y    = intval($bg_h/4) + 3;
                $pic_w   = intval($bg_w/2) - 5;
                $pic_h   = intval($bg_h/2) - 5;
                $space_x    = 5;
                break;
            case 3:
                $start_x    = 40;   // 开始位置X
                $start_y    = 5;    // 开始位置Y
                $pic_w   = intval($bg_w/2) - 5; // 宽度
                $pic_h   = intval($bg_h/2) - 5; // 高度
                $lineArr    = array(2);
                $line_x  = 4;
                break;
            case 4:
                $start_x    = 4;    // 开始位置X
                $start_y    = 5;    // 开始位置Y
                $pic_w   = intval($bg_w/2) - 5; // 宽度
                $pic_h   = intval($bg_h/2) - 5; // 高度
                $lineArr    = array(3);
                $line_x  = 4;
                break;
            case 5:
                $start_x    = 30;   // 开始位置X
                $start_y    = 30;   // 开始位置Y
                $pic_w   = intval($bg_w/3) - 5; // 宽度
                $pic_h   = intval($bg_h/3) - 5; // 高度
                $lineArr    = array(3);
                $line_x  = 5;
                break;
            case 6:
                $start_x    = 5;    // 开始位置X
                $start_y    = 30;   // 开始位置Y
                $pic_w   = intval($bg_w/3) - 5; // 宽度
                $pic_h   = intval($bg_h/3) - 5; // 高度
                $lineArr    = array(4);
                $line_x  = 5;
                break;
            case 7:
                $start_x    = 53;   // 开始位置X
                $start_y    = 5;    // 开始位置Y
                $pic_w   = intval($bg_w/3) - 5; // 宽度
                $pic_h   = intval($bg_h/3) - 5; // 高度
                $lineArr    = array(2,5);
                $line_x  = 5;
                break;
            case 8:
                $start_x    = 30;   // 开始位置X
                $start_y    = 5;    // 开始位置Y
                $pic_w   = intval($bg_w/3) - 5; // 宽度
                $pic_h   = intval($bg_h/3) - 5; // 高度
                $lineArr    = array(3,6);
                $line_x  = 5;
                break;
            case 9:
                $start_x    = 5;    // 开始位置X
                $start_y    = 5;    // 开始位置Y
                $pic_w   = intval($bg_w/3) - 5; // 宽度
                $pic_h   = intval($bg_h/3) - 5; // 高度
                $lineArr    = array(4,7);
                $line_x  = 5;
                break;
        }
        foreach( $pic_list as $k=>$pic_path ) {
            if (!$pic_path) {
                continue;
            }
            $kk = $k + 1;
            if ( in_array($kk, $lineArr) ) {
                $start_x    = $line_x;
                $start_y    = $start_y + $pic_h + $space_y;
            }
            if (isset($this->content_cache[$pic_path]) && $this->content_cache[$pic_path]) {
                $file_content = $this->content_cache[$pic_path];
            } else {
                $this->content_cache[$pic_path] = $file_content = file_get_contents($pic_path);
            }
            $resource = imagecreatefromstring($file_content);

            // $start_x,$start_y copy图片在背景中的位置
            // 0,0 被copy图片的位置
            // $pic_w,$pic_h copy后的高度和宽度
            imagecopyresized($background,$resource,$start_x,$start_y,0,0,$pic_w,$pic_h,imagesx($resource),imagesy($resource)); // 最后两个参数为原始图片宽度和高度，倒数两个参数为copy时的图片宽度和高度
            $start_x    = $start_x + $pic_w + $space_x;
        }
        ob_start();
        imagejpeg($background, null, 100);
        return ob_get_clean();
    }
}
