<?php

namespace app\models\bus\fullTimeBuses;

use yii\base\Model;

class FullTimeBusesTermsSchedules extends Model
{
    public $sentido; // "Aluche >> ETSIINF", "ETSIINF >> Aluche"
    public $listadoHoras = []; // ints array

    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            ['sentido', 'string'],
        ];
    }

}
