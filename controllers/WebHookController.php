<?php

namespace app\controllers;

use app\commands\base\Request;
use Yii;
use yii\web\Controller;

class WebHookController extends Controller
{

    function init()
    {
        set_time_limit ( 300 );
        parent::init();
    }

    public $enableCsrfValidation = false;

    public function actionIndex(){
        \Yii::$app->bot->handle();
    }

    public function actionDeploy()
    {
        $payload = json_decode(file_get_contents('php://input'));
        if($payload->ref === "refs/heads/master") {

            exec("cd /root/ETSIINFbot && ./deploy.sh 2>&1", $out, $ret);
            $mes = $ret ?
                "There was some error deploying " . Yii::$app->params['name'] . " v." . Yii::$app->params['version']
                : Yii::$app->params['name'] . " v." . Yii::$app->params['version'] . " deployed correctly";


            foreach (Yii::$app->params['admins'] as $id) {
                $req = new Request($id);
                $req->sendMessage($mes . "\n\n The output was:\n" . implode("\n", $out));
            }

        }
        else
        {
            foreach (Yii::$app->params['admins'] as $id) {
                $req = new Request($id);
                $req->sendMessage("There is a new push in branch ".$payload->ref);
            }
        }
    }

}
