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
            ['listadoHoras','safe']
        ];
    }

    public function setAttributes($values, $safeOnly = true)
    {
        parent::setAttributes($values, $safeOnly);

        $listadoHorasTemp = [];
        foreach($this->listadoHoras as $i=>$horasObject)
        {
            $horas = new FullTimeBusesSchedules();
            $horas->setAttributes($horasObject);

            if($horas->validate())
            {
                $listadoHorasTemp[$horasObject['dias']] = $horas;
            }

        }
        $this->listadoHoras = $listadoHorasTemp;


    }

}
