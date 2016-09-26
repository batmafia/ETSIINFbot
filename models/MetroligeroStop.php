<?php
/**
 * Created by PhpStorm.
 * User: Diego
 * Date: 23/9/16
 * Time: 23:28
 */

namespace app\models;
use yii\base\Model;
class MetroligeroData extends Model
{
    private $date;
    private $first_stop;
    private $second_stop;


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

    public function getFirstStopMinutes()
    {
        return round(($this->first_stop/60));
    }

    public function getSecondStopMinutes()
    {
        return round(($this->second_stop/60));
    }
}

