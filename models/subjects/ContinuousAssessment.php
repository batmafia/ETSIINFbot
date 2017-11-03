<?php
/**
 * Created by PhpStorm.
 * User: svg153
 * Date: 3/11/17
 * Time: 18:13
 */

namespace app\models\subjects;

use yii\base\Model;

class ContinuousAssessment extends Model
{
    public $actividades = [];

/*
"evaluacion_continua": {
    "281374": {
        "SEMANA": "5",
        "DENOMINACION": "Entrega práctica 1",
        "DURACION": "05:00",
        "TECNICA": "TG: Técnica del tipo Trabajo en Grupo",
        "PESO": "20",
        "NOTA_MINIMA": "4",
        "PRESENCIAL": "No Presencial"
    },
    ...
    "281393": {
        "SEMANA": "4",
        "DENOMINACION": "Control individual 1",
        "DURACION": "02:00",
        "TECNICA": "TI: Técnica del tipo Trabajo Individual",
        "PESO": "10",
        "NOTA_MINIMA": "4",
        "PRESENCIAL": "No Presencial"
    }
}
*/

    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            ['activities', 'safe'],
        ];
    }

    public function setAttributes($values, $safeOnly = true)
    {
        parent::setAttributes($values, $safeOnly);

        $actividades = [];
        foreach($this->evaluacion_continua as $k=>$v)
        {
            $activity = new Activities();
            $activity->setAttributes($v);
            if($activity->validate())
            {
                $actividades[$k] = $activity;
            }

        }
        $this->actividades = $actividades;

    }
}
