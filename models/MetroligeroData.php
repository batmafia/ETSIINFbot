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

    public function getArrivals()
    {
        $first_stop=round(($this->first_stop/60));
        $second_stop=round(($this->second_stop/60));

        return "Próximo tren llegará en $first_stop min.\n
                Siguiente tren llegará en $second_stop min.";
    }
}

