<?php
/**
 * 金流日志记录
 * Author: huangkaixuan
 * Date: 2017/12/27
 * Time: 10:37
 */

namespace App\Pay;


use Illuminate\Log\Writer;
use Monolog\Logger;

class PayLogger
{
    private static $withdrawLogger;
    private static $depositLogger;
    private static $commonLogger;

    /**
     * 通用日志
     * @return Writer
     */
    public static function common()
    {
        if (!self::$commonLogger) {
            self::$commonLogger = self::genLogger('pay-common', 20);
        }
        return self::$commonLogger;
    }

    /**
     * 生成日志记录器
     * @param $type
     * @param int $preserve_days
     * @return Writer
     */
    private static function genLogger($type, $preserve_days = 15)
    {
        $logger = new Writer(new Logger($type));
        $logger->useDailyFiles(storage_path() . '/logs/' . $type . '.log', $preserve_days);
        return $logger;
    }

    /**
     * 取得提现日志
     * @return Writer
     */
    public static function withdraw()
    {
        if (!self::$withdrawLogger) {
            self::$withdrawLogger = self::genLogger('withdraw', 20);
        }
        return self::$withdrawLogger;
    }

    /**
     * 取得充值日志
     * @return Writer
     */
    public static function deposit()
    {
        if (!self::$depositLogger) {
            self::$depositLogger = self::genLogger('deposit');
        }
        return self::$depositLogger;
    }
}