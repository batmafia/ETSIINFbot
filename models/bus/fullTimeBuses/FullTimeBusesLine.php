<?php

namespace app\models\bus\fullTimeBuses;

use yii\base\Model;

class FullTimeBusesLine extends Model
{
    public $idLinea; // ints 591, 865, 571, 573
    public $periodos = []; // ints array


    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            ['idLinea', 'integer'],
        ];
    }

}
