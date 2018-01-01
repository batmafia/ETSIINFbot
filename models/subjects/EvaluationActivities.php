<?php
/**
 * Created by PhpStorm.
 * User: svg153
 * Date: 3/11/17
 * Time: 17:53
 */

namespace app\models\subjects;

use yii\base\Model;

class EvaluationActivities extends Model
{
    public $evaluacion_continua = [];
    public $prueba_final = [];

/*
"actividades_evaluacion": {
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
    },
    "prueba_final": {
        "281366": {
            "SEMANA": "17",
            "DENOMINACION": "Examen FINAL",
            "DURACION": "03:00",
            "TECNICA": "EX: Técnica del tipo Examen Escrito",
            "PESO": "100",
            "NOTA_MINIMA": "5",
            "PRESENCIAL": "Presencial"
        }
    }
}
*/

    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            ['evaluacion_continua', 'safe'],
            ['prueba_final', 'safe'],
        ];
    }

    public function setAttributes($values, $safeOnly = true)
    {
        parent::setAttributes($values, $safeOnly);

        $evaluacion_continua = [];
        foreach($this->evaluacion_continua as $k=>$v)
        {
            $continuousAssessment = new ContinuousAssessment();
            $continuousAssessment->setAttributes($v);
            if($continuousAssessment->validate())
            {
                $evaluacion_continua[] = $continuousAssessment;
            }

        }
        $this->evaluacion_continua = $evaluacion_continua;

        $prueba_final = [];
        foreach($this->prueba_final as $k=>$v)
        {
            $finalTest = new FinalTest();
            $finalTest->setAttributes($v);
            if($finalTest->validate())
            {
                $prueba_final[] = $finalTest;
            }

        }
        $this->prueba_final = $prueba_final;

    }
}
