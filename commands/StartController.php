<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace app\commands;

use app\Components\TelegramBot;
use Longman\TelegramBot\Request;
use Symfony\Component\Console\Exception\RuntimeException;
use yii\console\Controller;

class StartController extends Controller
{
    /**
     * This command starts the bot.
     * Yet to be implemented
     */
    public function actionHook($baseAddress, $port = 8443)
    {
        shell_exec('openssl req -newkey rsa:2048 -sha256 -nodes -keyout etsiinfbot.key -x509 -days 365 -out etsiinfbot.pem -subj "/C=ES/ST=Madrid/L=Madrid/O=ETSIINF/CN='.escapeshellarg($baseAddress).'"');
        \Yii::$app->bot->unsetWebHook();
        $result = \Yii::$app->bot->setWebhook("https://$baseAddress:$port", "etsiinfbot.pem");

        /** @var $result Request */
        if($result->isOk()){
            echo $result->getDescription(), "\n";
        } else {
            throw new RuntimeException("Error setting WebHook");
        }
    }

    public function actionUpdates()
    {
        \Yii::$app->bot->handleGetUpdates();
    }
}
