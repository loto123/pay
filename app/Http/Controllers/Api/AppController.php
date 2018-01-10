<?php

namespace App\Http\Controllers\Api;

use App\User;
use App\Version;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
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
     *                  @SWG\Property(property="need_upgrade", type="boolean", example=0,description="是否需要升级"),
     *                  @SWG\Property(property="upgrade_type", type="boolean", example=1,description="升级类型 0=普通更新 1=强制更新"),
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
        $last_version = Version::where("platform", $request->platform == 'ios' ? 0:1)->where("ver_code", ">", $request->ver_code)->orderBy("ver_code", "DESC")->first();
        $result = ['need_upgrade' => 0, 'upgrade_type' => 0, 'changelog' => "", 'download_url' => ''];
        /* @var $user User */
        if ($last_version) {
            $result['need_upgrade'] = 1;
            $result['changelog'] = $last_version->changelog;
            $result['download_url'] = Storage::disk(config('admin.upload.disk'))->url($last_version->url);
            list($major,) = explode('.', $request->ver_code);
            list($last_version_major,) = explode('.', $last_version->ver_code);
            if ($last_version_major > $major) {
                $result['upgrade_type'] = 1;
            }
        }
        return $this->json($result);
    }


}