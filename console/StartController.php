<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace app\console;

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
    public function actionHook($address = 'https://batmafia.frildoren.com/?r=web-hook')
    {
        $result = \Yii::$app->bot->setWebhook($address);

        /** @var $result Request */
        if($result->isOk()){
            echo $result->getDescription(), "\n";
        } else {
            throw new RuntimeException("Error setting WebHook");
        }
    }

    public function actionStopHook()
    {
        \Yii::$app->bot->unsetWebHook();
    }

    public function actionUpdates($timeout=1)
    {
        while(\Yii::$app->bot->handleGetUpdates())
            sleep($timeout);
    }
}
