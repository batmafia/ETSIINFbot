<?php
/**
 * Created by PhpStorm.
 * User: frildoren
 * Date: 11/10/16
 * Time: 17:22
 */

namespace app\controllers;


use yii\base\Controller;

class StatsController extends Controller
{

    public function actionIndex()
    {
        $result = \Yii::$app->getDb()->createCommand(
            "SELECT DATE(date) as day, COUNT(*) as requests, COUNT(DISTINCT user_id) as users FROM message WHERE DATE(date) > NOW()-INTERVAL 30 DAY AND LEFT(text, 1) = '/' GROUP BY day;"
        )->queryAll();

        $data = [];
        foreach ($result as $r)
        {
            $data['days'][] = explode("-", $r['day'])[2];
            $data['requests'][] = intval($r['requests']);
            $data['users'][] = intval($r['users']);
        }

        return $this->render('index', $data);
    }

}