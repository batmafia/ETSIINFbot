<?php

namespace app\models\bus\fullTimeBuses;

use yii\base\Model;

class FullTimeBusesSchedules extends Model
{
    public $dias; // "12345"
    public $horas = []; // string arary "07:30", "08:00", "08:20",...

    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            ['dias', 'integer'],
            ['horas', 'each', 'rule' => ['string']]
        ];
    }

}
