<?php
/**
 * Created by PhpStorm.
 * User: Diego
 * Date: 23/9/16
 * Time: 23:28
 */

namespace app\models;
use yii\base\Model;
class MetroligeroStop extends Model
{
    public $date;
    public $first_stop;
    public $second_stop;

    /*"date":"Fri, 23 Sep 2016 21:34:24 +0200",
    "first_stop":695,
    "second_stop":1474*/
    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            [['date','first_stop','second_stop'], 'string']
        ];
    }

    private function getIntervalo(){

        date_default_timezone_set('Europe/Madrid');

        $datetime1 = new \DateTime($this->date);
        $datetime2 = new \DateTime(date('r'));

        $interval = date_diff($datetime2, $datetime1);
        return $interval->format('%i');

    }

    public function getArrivals(){

        $intervalo = $this->getIntervalo();
        $myFirstStop = round(($this->first_stop/60)-$intervalo);
        $mySecondStop = round(($this->second_stop/60)-$intervalo);

        $arrivals [0] = $myFirstStop;
        $arrivals [1] = $mySecondStop;

        return $arrivals;
    }
}

