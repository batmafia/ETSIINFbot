<?php

namespace app\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\filters\VerbFilter;
use app\models\LoginForm;
use app\models\ContactForm;

class SiteController extends Controller
{

    public $defaultAction = 'web-hook';

    public function actionWebHook(){
        $this->enableCsrfValidation = false;
        \Yii::$app->bot->handle();
    }
}
