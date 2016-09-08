<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace app\commands;

use app\Components\TelegramBot;
use yii\console\Controller;

/**
 * This command echoes the first argument that you have entered.
 *
 * This command is provided as an example for you to learn how to create console commands.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class StartController extends Controller
{
    /**
     * This command starts the bot.
     * Yet to be implemented
     */
    public function actionIndex($baseAddress, $port = 13000)
    {
        \Yii::$app->bot->setWebhook("https://$baseAddress:$port");
    }
}
