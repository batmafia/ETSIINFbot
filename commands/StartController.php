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
    public function actionIndex($baseAddress, $port = 8443)
    {
        shell_exec('openssl req -newkey rsa:2048 -sha256 -nodes -keyout etsiinfbot.key -x509 -days 365 -out etsiinfbot.pem -subj "/C=ES/ST=Madrid/L=Madrid/O=ETSIINF/CN='.$baseAddress.'"');
        \Yii::$app->bot->setWebhook("https://$baseAddress:$port", "etsiinfbot.pem");
    }
}
