<?php

namespace app\models\bus\fullTimeBuses;

use yii\base\Model;

class FullTimeBusesTermsSchedules extends Model
{
    public $dias; // "Aluche >> ETSIINF", "ETSIINF >> Aluche"
    public $horas = []; // string arary "07:30", "08:00", "08:20",...

    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            ['dias', 'integer'],
            ['horas', 'each', 'rule' => ['string']],
        ];
    }

}
