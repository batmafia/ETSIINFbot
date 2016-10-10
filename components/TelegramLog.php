<?php
/**
 * Created by PhpStorm.
 * User: frildoren
 * Date: 10/10/16
 * Time: 11:50
 */

namespace app\Components;


use app\commands\base\Request;
use yii\log\Target;

class TelegramLog extends Target
{

    /**
     * Exports log [[messages]] to a specific destination.
     * Child classes must implement this method.
     */
    public function export()
    {
        $text = implode("\n", array_map([$this, 'formatMessage'], $this->messages)) . "\n";

        foreach (Yii::$app->params['admins'] as $id) {
            $req = new Request($id);
            $req->sendMessage($text);
        }
    }
}