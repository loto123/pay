<?php

namespace App\Http\Controllers\Api;

use App\Version;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

/**
 *
 * @package App\Http\Controllers\Api
 */
class AppController extends BaseController {

    /**
     * @SWG\Get(
     *   path="/app/version",
     *   summary="版本检测",
     *   tags={"App"},
     *   @SWG\Parameter(
     *     name="platform",
     *     in="query",
     *     description="平台 ios或android",
     *     required=true,
     *     type="string"
     *   ),
     *   @SWG\Parameter(
     *     name="ver_code",
     *     in="query",
     *     description="版本号数字",
     *     required=true,
     *     type="integer"
     *   ),
     *     @SWG\Response(
     *          response=200,
     *          description="成功返回",
     *          @SWG\Schema(
     *              @SWG\Property(
     *                  property="code",
     *                  type="integer",
     *                  example=1
     *              ),
     *              @SWG\Property(
     *                  property="msg",
     *                  type="string"
     *              ),
     *              @SWG\Property(
     *                  property="data",
     *                  type="object",
     *                  @SWG\Property(property="version", type="string", example="2.3.4",description="最新版本名"),
     *                  @SWG\Property(property="type", type="integer", example=1,description="升级类型 0=不用更新 1=普通更新 2=强制更新"),
     *                  @SWG\Property(property="changelog", type="string", example="标题
正文",description="更新日志"),
     *                  @SWG\Property(property="download_url", type="string", example="url",description="下载地址"),
     *              )
     *          )
     *      ),
     *      @SWG\Response(
     *         response="default",
     *         description="错误返回",
     *         @SWG\Schema(ref="#/definitions/ErrorModel")
     *      )
     * )
     * @return \Illuminate\Http\Response
     */
    public function version(Request $request){
        $validator = Validator::make($request->all(), [
            'platform' => ['required', Rule::in(['ios', 'android'])],
            'ver_code' => 'required'
        ]);

        if ($validator->fails()) {
            return $this->json([], $validator->errors()->first(), 0);
        }
        $last_version = Version::where("platform", $request->platform == 'ios' ? Version::PLATFORM_IOS : Version::PLATFORM_ANDROID)->where("ver_code", ">", $request->ver_code)->orderBy("ver_code", "DESC")->first();
        $result = ['version' => "", 'type' => Version::TYPE_DEFAULT, 'changelog' => "", 'download_url' => ''];
        $client_version = Version::where("ver_code", $request->ver_code)->first();
        if ($last_version) {
            $result['type'] = Version::TYPE_UPGRADE;
            $result['version'] = $last_version->ver_name;
            $result['changelog'] = $last_version->changelog;
            $result['download_url'] = $last_version->url;
            if ($client_version) {
                list($major,) = explode('.', $client_version->ver_code);
                list($last_version_major,) = explode('.', $last_version->ver_code);
                if ($last_version_major > $major) {
                    $result['type'] = Version::TYPE_FORCE_UPGRADE;
                }
            } else {
                $result['type'] = Version::TYPE_FORCE_UPGRADE;
            }
        }
        return $this->json($result);
    }


}