<?php
/**
 * Created by PhpStorm.
 * User: Diego
 * Date: 20/9/16
 * Time: 12:31
 */

namespace app\models\subjects;

use yii\base\Model;

class Subject extends Model
{

    public $codigo;
    public $nombre;
    public $ects;
    public $anio;
    public $semestre;
    public $guia;
    public $depto;
    public $plan;
    public $caracter;
    public $profesores = [];
    public $recursos_didacticos = [];
    public $actividades_evaluacion = [];
    public $criterios_evaluacion;
    public $fecha_actualizacion;


    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            [['nombre','anio','semestre','guia','depto','plan','caracter','criterios_evaluacion','fecha_actualizacion'], 'string'],
            [['profesores','recursos_didacticos','actividades_evaluacion'], 'safe'],
            ['ects', 'double'],
            ['codigo', 'integer'],
        ];
    }

    public function validateModels($attribute, $value)
    {
        return $this->{$attribute}->validate();
    }

    public function setAttributes($values, $safeOnly = true)
    {
        parent::setAttributes($values, $safeOnly);

        $profesores = [];
        foreach($this->profesores as $i=>$p)
        {
            $profesor = new Teacher();
            $profesor->setAttributes($p);
            if($profesor->validate())
            {
                $profesores[] = $profesor;
            }

        }
        $this->profesores = $profesores;

        $recursos_didacticos = [];
        foreach($this->recursos_didacticos as $i=>$k)
        {
            $didacticResources = new DidacticResources();
            $didacticResources->setAttributes($k);
            if($didacticResources->validate())
            {
                $recursos_didacticos[] = $didacticResources;
            }

        }
        $this->recursos_didacticos = $recursos_didacticos;

        $actividades_evaluacion = [];
        foreach($this->actividades_evaluacion as $i=>$k)
        {
            $evaluationActivity = new EvaluationActivities();
            $evaluationActivity->setAttributes($k);
            if($evaluationActivity->validate())
            {
                $actividades_evaluacion[] = $evaluationActivity;
            }

        }
        $this->actividades_evaluacion = $actividades_evaluacion;

    }


}
