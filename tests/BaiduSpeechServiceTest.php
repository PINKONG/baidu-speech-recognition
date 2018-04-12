<?php

namespace Tests;

use Pinkong\BaiduSpeechRecognition\SpeechHelper;
//use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

class BaiduSpeechServiceTest extends TestCase
{
    /**
     * Creates the application.
     *
     * @return \Illuminate\Foundation\Application
     */
    public function createApplication()
    {
        $app = require __DIR__.'/../vendor/laravel/laravel/bootstrap/app.php';
        $app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
        $this->registerServiceProviders($app);
        return $app;
    }

    protected function registerServiceProviders($app) {
        $app->register('Pinkong\BaiduSpeechRecognition\BaiduSpeechServiceProvider');
    }

    /**
     * 语音转码
     *
     * @return string
     */
    public function testConvertMp3FileToPCM()
    {
        $callStartTime = microtime(true);

        $result = SpeechHelper::convertMp3FileToPCM(__DIR__.'/test4.mp3');

        $callEndTime = microtime(true);
        $callTime = $callEndTime - $callStartTime;

        unlink($result);

        var_dump($result);

        echo "\n";
        echo '语音转码共耗时: ' , sprintf('%.4f',$callTime) , " seconds";

        $this->assertNotEmpty($result);
    }

    /**
     * 语音识别
     *
     * @return string
     */
    public function testCallSpeechRecognition()
    {
        $callStartTime = microtime(true);

        $file = SpeechHelper::convertMp3FileToPCM(__DIR__.'/test4.mp3');
        $result = SpeechHelper::callSpeechRecognition($file);

        $callEndTime = microtime(true);
        $callTime = $callEndTime - $callStartTime;

        var_dump($result);

        unlink($file);

        echo "\n";
        echo '语音识别共耗时: ' , sprintf('%.4f',$callTime) , " seconds";

        $this->assertNotEmpty($result);
    }

    /**
     * 语音匹配
     *
     * @return string
     */
    public function testVerifyVoiceAndTextMatching()
    {
        $callStartTime = microtime(true);

//        $expectedText = '测试一下测试一下';
        $expectedText = '新年新气象';
        $resultText = SpeechHelper::verifyVoiceAndTextMatching(__DIR__.'/test3.mp3', $expectedText);

        $callEndTime = microtime(true);
        $callTime = $callEndTime - $callStartTime;
        echo "\n";
        echo '语音识别共耗时: ' , sprintf('%.4f',$callTime) , " seconds";

        $this->assertEquals($expectedText, $resultText);
    }

}
