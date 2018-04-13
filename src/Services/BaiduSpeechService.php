<?php
/**
 * Created by PhpStorm.
 * User: sunpq
 * Date: 2018/1/29
 * Time: 下午2:16
 */

namespace Pinkong\BaiduSpeechRecognition\Services;

require_once __DIR__ . '/../baidu-speech-sdk/AipSpeech.php';

use AipSpeech;
use Illuminate\Support\Facades\Storage;
use Pinkong\BaiduSpeechRecognition\Exceptions\Exception;


/**
 * 百度语音识别
 * Class BaiduSpeechService
 * @package App\Services
 */
class BaiduSpeechService
{
    private $appId;
    private $appKey;
    private $appSecret;
    private $client;

    private static $errorMapping = array(
        0    => 'success',
        400  => '未知错误',
        404  => '音频文件找不到',
        3300 => '输入参数不正确',
        3301 => '音频质量过差',
        3302 => '鉴权失败',
        3303 => '语音服务器后端问题',
        3304 => '用户的请求QPS超限',
        3305 => '用户的日pv（日请求量）超限',
        3307 => '语音服务器后端识别出错问题',
        3308 => '音频过长',
        3309 => '音频数据问题',
        3310 => '输入的音频文件过大',
        3311 => '采样率rate参数不在选项里',
        3312 => '音频格式format参数不在选项里',
    );


    public function __construct()
    {
        $this->appId = config('baiduspeech.app_id');
        $this->appKey = config('baiduspeech.app_key');
        $this->appSecret = config('baiduspeech.app_secret');
        $this->client = new AipSpeech($this->appId, $this->appKey, $this->appSecret);
    }

    private static function errorMessageWithCode($errorCode) {
        if(array_key_exists($errorCode, self::$errorMapping)){
            return self::$errorMapping[$errorCode];
        }
        return '未知错误';
    }

    /**
     * 缓存语音文件， 并返回文件路径
     *
     * @param [type] $file
     * @return void
     */
    private static function storeFileToLocal($file)
    {
        //文件命名
        $fileName = md5(uniqid()) . '.' . $file->getClientOriginalExtension();
        $dir = "voices";
        $voiceSrc = $dir . '/' . $fileName;

        $storage = Storage::disk('local');
        $storage->putFileAs($voiceSrc, $file, '');
        $voiceFile = Storage::disk('local')->path($voiceSrc);

        return $voiceFile;
    }

    /**
     * 发送语音识别请求
     */
    public function callSpeechRecognition($filename)
    {
        $result = array(
            'err_no' => 0,
            'result' =>[],
        );

        if (!file_exists($filename)) {
            $result['err_no'] = 404;
        }
        else {
            //请求百度API
            try {
                $response = $this->client->asr(file_get_contents($filename), 'pcm', 16000, array(
                    'lan' => 'zh',
                ));

                $result['err_no'] = $response['err_no'];
                $result['result'] = array_key_exists('result', $response) ? $response['result'] : [];

                //发现有err_no是0但是结果是空的情况
                if ($result['err_no'] == 0 && empty($result['result'])) {
                    $result['err_no'] = 400;
                }

            } catch (\Exception $e) {
                $result['err_no'] = 400;
            }
        }

        $result['err_msg'] = self::errorMessageWithCode($result['err_no']);
        //var_dump($result);

        return $result;
    }

    /**
     * 调用ffmepg转码
     */
    public function convertMp3FileToPCM($sourceFile)
    {
        $filePath = pathinfo($sourceFile, PATHINFO_DIRNAME);
        $fileName = pathinfo($sourceFile, PATHINFO_FILENAME);
        $fileExtension = pathinfo($sourceFile, PATHINFO_EXTENSION);
        $destName = $fileName.'.pcm';

        //源文件不存在
        if (!file_exists($sourceFile)) {
            throw Exception::speechRawVoiceNotExistException();
        }
        //源文件不是mp3
        if (strcasecmp($fileExtension, 'mp3') != 0) {
            throw Exception::speechRawVoiceNotFormatInvalidException();
        }

        //不支持shell_execute
        try {
            $destFile = $filePath.'/'.$destName;
            $shell = "ffmpeg -y  -i ".$sourceFile." -acodec pcm_s16le -f s16le -ac 1 -ar 16000 ".$destFile;
            $shellExec = shell_exec($shell);
        }
        catch (\ErrorException $exception) {
            unlink($sourceFile);
            throw Exception::shellExecuteFailException();
        }

        if (!file_exists($destFile)) {
            throw Exception::speechConvertFailException();
        }

        return $destFile;
    }

    /**
     * 获取语音文本
     * @param  $file 语音文件对象
     * @return string
     * @throws
     */
    public function obtainVoiceText($file)
    {
        //保存原始语音文件到本地，用于转码和语音识别
        $voiceFile = self::storeFileToLocal($file);

        //先转码
        $pcmFile = $this->convertMp3FileToPCM($voiceFile);

        //语音识别
        $recognitionResult = $this->callSpeechRecognition($pcmFile);

        //检查识别结果
        if($recognitionResult['err_no'] != 0) {
            unlink($voiceFile);

            $message = $recognitionResult['err_msg'];
            throw Exception::speechRecognitionFailException('语音识别失败'.$message);
        }

        //删除文件
        unlink($pcmFile);
        unlink($voiceFile);

        return $recognitionResult;
    }


    /**
     * 校验语音和指定的文本是否匹配
     * @param string $voiceFile 语音文件路径
     * @param string $text 指定要匹配的文本
     * @return string
     * @throws
     */
    public function verifyVoiceAndTextMatching($voiceFile, $text)
    {
        //语音文本
        $recognitionResult = $this->obtainVoiceText($voiceFile);

        //检查识别结果
        if($recognitionResult['err_no'] != 0) {
            $message = $recognitionResult['err_msg'];
            throw Exception::speechRecognitionFailException('语音识别失败'.$message);
        }
        $voiceContents = $recognitionResult['result'];

        //查找匹配的文本
        $matchedText = null;
        foreach ($voiceContents as $voice) {
            $emptyChars = " ,.，。";
            $trimed = preg_replace('/^['.$emptyChars.']*(?U)(.*)['.$emptyChars.']*$/u', '\\1', $voice);
            if (strcasecmp($trimed, $text) == 0) {
                $matchedText = $trimed;
                break;
            }
        }

        //未匹配到
        if (!isset($matchedText)) {
            throw Exception::speechNotMatchTextFailException();
        }

        return $matchedText;
    }

}