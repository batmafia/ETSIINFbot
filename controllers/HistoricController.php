<?php
/**
 * Created by PhpStorm.
 * User: frildoren
 * Date: 11/10/16
 * Time: 17:22
 */

namespace app\controllers;


use yii\base\Controller;

class HistoricController extends Controller
{

    public function actionIndex()
    {
        $notIds = implode(array_merge(\Yii::$app->params['admins'], \Yii::$app->params['moderators']), ",");
        $result = \Yii::$app->getDb()->createCommand(
            "SELECT 
                DATE(date) AS day,
                COUNT(*) AS requests,
                COUNT(DISTINCT user_id) AS users
            FROM message
            WHERE
                user_id NOT IN (".$notIds.")
                AND LEFT(text, 1) = '/'
            GROUP BY day;"
        )->queryAll();

        $data = [
            'requests'=>[],
            'users'=>[],
            'series'=>[],
            'days'=>[]
        ];

        foreach ($result as $r)
        {
            $name = date("d M", strtotime($r['day']));
            $serie = [
                'name'=>$name,
                'id'=>$name,
                'type'=>'pie'
            ];

            $data['days'][] = $name;
            $data['requests'][] = ['name'=>$name, 'y'=>intval($r['requests']), 'drilldown'=>$name];
            $data['users'][] = ['name'=>$name, 'y'=>intval($r['users'])];

            $result2 = \Yii::$app->getDb()->createCommand(
                "SELECT
                    COUNT(*) as count,
                    case when LOCATE('@', text) OR LOCATE(' ', text) then LEFT(text, LOCATE('@', text)+LOCATE(' ', text)-1) else text end as command
                FROM message
                WHERE LEFT(text, 1) = '/'
                AND user_id NOT IN (".$notIds.")
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

/*
    public function acctionHistoric()
    {
        $notIds = implode(array_merge(\Yii::$app->params['admins'], \Yii::$app->params['moderators']), ",");

        $data = [
            'series'=>[],
        ];

        $serie = [
            'name'=>'Historic',
            'id'=>'Historic',
            'type'=>'pie'
        ];

        $result = \Yii::$app->getDb()->createCommand(
            "SELECT
            COUNT(*) as count,
            case when LOCATE('@', text) OR LOCATE(' ', text) then LEFT(text, LOCATE('@', text)+LOCATE(' ', text)-1) else text end as command
            FROM message
            WHERE LEFT(text, 1) = '/'
            AND user_id NOT IN (".$notIds.")
            GROUP BY command
            ORDER BY count ASC"
        )->queryAll();


        foreach ($result as $r2)
        {
            $serie['data'][] = [$r2['command'], intval($r2['count'])];
        }

        $data['series'][] = $serie;


        return $this->render('index', $data);
    }
*/
}