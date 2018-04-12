<?php
/**
 * Created by PhpStorm.
 * User: sunpq
 * Date: 2018/1/29
 * Time: 下午2:16
 */

namespace Pinkong\BaiduSpeechRecognition;

use Illuminate\Support\Facades\Facade;

class SpeechHelper extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'BaiduSpeechHelper';
    }
}