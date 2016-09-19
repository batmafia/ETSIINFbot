<?php

namespace app\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\filters\VerbFilter;
use app\models\LoginForm;
use app\models\ContactForm;

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
        }
    }
}
