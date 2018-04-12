# baidu-speech-recognition

---
这个Laravel\Lumen扩展包使用百度云语音识别API，提供一个简单的方法从语音文件中提取文本。代码基于官方php sdk构建。

使用前，请先前往[百度AI开放平台](http://ai.baidu.com/)，找到`人工智能`下的`百度语音`，创建应用。
百度的语言识别api是免费的，默认有50000/天的调用限制，需要的话可以申请提高配额到1000000/天。

由于百度的api只接受特定码率的文件，所以代码中使用ffmepg进行了转码。
所以有个需要注意的地方：
* 本地必须安装ffmepg
* PHP必须有shell_exec执行权限

## Precondition
To use this package, you should go to , find `人工智能`->`百度语音` and create a applicaton,then get the AppID, API Key, and Secret Key。

## Installation

Require this package with composer by using the following command:

```
$ composer require pinkong/baidu-speech-recognition
```

Then, add the service provider:

If you are using Laravel, add the service provider to the providers array in `config/app.php`:

```php
[
    'providers' => [
        Pinkong\BaiduSpeechRecognition\BaiduSpeechServiceProvider::class,
    ],
]
```

as optional, you can use facade:
```php
    'aliases' => [
        'SpeechHelper' => Pinkong\BaiduSpeechRecognition\SpeechHelper::class,
    ],

```

If you are using Lumen, append the following code to `bootstrap/app.php`:

```php
$app->register(Pinkong\BaiduSpeechRecognition\BaiduSpeechServiceProvider::class);
```

## configuration
---
The defaults are set in config/baiduspeech.php. Copy this file to your own config directory to modify the values. You can publish the config using this command:

```php
php artisan vendor:publish --provider="Pinkong\BaiduSpeechRecognition\BaiduSpeechServiceProvider"

```

If you are using Lumen, append the following code to `bootstrap/app.php`:

```php
$app->configure('baiduspeech');
```

## Usage
```php
$result = SpeechHelper::convertMp3FileToPCM('test4.mp3');
```

## License

The Laravel-Swoole-Http package is open-sourced software licensed under the [MIT license](http://opensource.org/licenses/MIT).