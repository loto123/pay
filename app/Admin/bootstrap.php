<?php

/**
 * Laravel-admin - admin builder based on Laravel.
 * @author z-song <https://github.com/z-song>
 *
 * Bootstraper for Admin.
 *
 * Here you can remove builtin form field:
 * Encore\Admin\Form::forget(['map', 'editor']);
 *
 * Or extend custom form field:
 * Encore\Admin\Form::extend('php', PHPEditor::class);
 *
 * Or require js and css assets:
 * Admin::css('/packages/prettydocs/css/styles.css');
 * Admin::js('/packages/prettydocs/js/main.js');
 *
 */

use App\Admin\Extensions\WangEditor;
use Encore\Admin\Form;

Encore\Admin\Form::forget(['map', 'editor']);

//Admin::css('/css/backstage.css');
Admin::css('/css/daterangepicker-bs3.css');
Admin::css('/css/common.css');
Admin::css('/css/admin.css');
Admin::css('/css/buildPet.css');

Admin::js('/js/buildPet.js');
Admin::js('/js/moment.js');
Admin::js('/js/daterangepicker.js');
Admin::js('/js/layer/layer.js');

Form::extend('editor', WangEditor::class);