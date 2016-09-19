<?php

namespace app\controllers;

use app\commands\base\Request;
use Yii;
use yii\web\Controller;

class WebHookController extends Controller
{

    public $enableCsrfValidation = false;

    public function actionIndex(){
        \Yii::$app->bot->handle();
    }

    public function actionDeploy($token)
    {
        if($token === 'ivb3iuwet7wai3292')
        {
            exec(Yii::$app->basePath . "/deploy.sh", $out, $ret);
            $mes = $ret ?
                "There was some error deploying ".Yii::$app->params['name']." v.".Yii::$app->params['version']
                : Yii::$app->params['name']." v.".Yii::$app->params['version']." deployed correctly";

            foreach(Yii::$app->params['admins'] as $id)
            {
                $req = new Request($id);
                $req->sendMessage($mes);
            }
        }
    }
}
