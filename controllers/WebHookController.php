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

    public $enableCsrfValidation = false;

    public function actionIndex(){
        \Yii::$app->bot->handle();
    }
}
