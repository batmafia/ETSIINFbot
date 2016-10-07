<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace app\console;

use app\commands\base\Request;
use app\models\repositories\BusRepository;
use app\models\repositories\MenuRepository;
use yii\console\Controller;

class TestController extends Controller
{
    public function actionGetBus()
    {

    }

    public function actionTest()
    {
        $req = new Request(\Yii::$app->params['admins']['Fril']);
        $req->sendMessage("hi");
    }

    public function actionCafeta()
    {
        $link = MenuRepository::getLastPdfLink();

        $req = new Request(\Yii::$app->params['admins']['Fril']);
        $req->sendDocument($link, 'Prueba');
    }
}
