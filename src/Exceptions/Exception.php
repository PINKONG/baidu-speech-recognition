<?php

namespace Pinkong\BaiduSpeechRecognition\Exceptions;

use Illuminate\Http\Response;

class Exception extends \Exception
{

    public $httpCode;

    public function __construct($message = "", $code = 0, $httpCode = 400, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->httpCode = $httpCode;
    }


    public function present()
    {
        $data = [];
        if ($this->code) {
            $data['code'] = $this->code;
        }
        if ($this->message) {
            $data['message'] = $this->message;
        }
        return $data;
    }

    /**
     * 生成异常返回
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getResponse()
    {
        $meta = $this->present();
        if ($meta) {
            $body = json_encode(['meta' => $meta]);
        } else {
            $body = null;
        }
        return Response::create($body, $this->httpCode, ['Content-Type' => 'application/json']);
    }

    public static function speechRawVoiceNotExistException($msg='原始语音文件不存在')
    {
        return new Exception($msg, $code = 20001);
    }

    public static function speechRawVoiceNotFormatInvalidException($msg='原始语音文件类型不正确')
    {
        return new Exception($msg, $code = 20002);
    }

    public static function speechConvertFailException($msg='语音转码失败')
    {
        return new Exception($msg, $code = 20003);
    }

    public static function speechRecognitionFailException($msg='语音识别失败')
    {
        return new Exception($msg, $code = 20004);
    }

    public static function speechNotMatchTextFailException($msg='语音匹配文本失败')
    {
        return new Exception($msg, $code = 20005);
    }

    public static function shellExecuteFailException($msg='无法调用FFmepg转码')
    {
        return new Exception($msg, $code = 20006);
    }
}