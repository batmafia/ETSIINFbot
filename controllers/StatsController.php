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
            $name = date("d M", strtotime($r['day']));
            $serie = [
                'name'=>$name,
                'id'=>$name,
                'type'=>'column'
            ];

            $data['days'][] = $name;
            $data['requests'][] = ['name'=>$name, 'y'=>intval($r['requests']), 'drilldown'=>$name];
            $data['users'][] = ['name'=>$name, 'y'=>intval($r['users'])];

            $result2 = \Yii::$app->getDb()->createCommand(
                "SELECT
                    COUNT(*) as count,
                    case when LOCATE('@', text) OR LOCATE(' ', text) then LEFT(text, LOCATE('@', text)+LOCATE(' ', text)-1) else text end as command
                FROM message
                WHERE LEFT(text, 1) = '/' AND DATE(date) = \"".$r['day']."\"
                GROUP BY command
                ORDER BY count ASC"
            )->queryAll();

            foreach ($result2 as $r2)
            {
                $serie['data'][] = [$r2['command'], intval($r2['count'])];
            }

            $data['series'][] = $serie;
        }

        return $this->render('index', $data);
    }

}