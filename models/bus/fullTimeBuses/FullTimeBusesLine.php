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
            ['idLinea', 'string'],
            ['periodos', 'safe']
        ];
    }

    public function setAttributes($values, $safeOnly = true)
    {
        parent::setAttributes($values, $safeOnly);

        $periodosTemp = [];
        foreach($this->periodos as $i=>$periodoObject)
        {
            $periodo = new FullTimeBusesTerms();
            $periodo->setAttributes($periodoObject);

            if($periodo->validate())
            {
                $periodosTemp[$periodoObject['idPeriodo']] = $periodo;
            }
        }

        $this->periodos = $periodosTemp;

    }


}
