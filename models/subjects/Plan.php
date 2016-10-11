<?php
/**
 * Created by PhpStorm.
 * User: Diego
 * Date: 23/9/16
 * Time: 17:42
 */

namespace app\models\subjects;

use yii\base\Model;

class Plan extends Model
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
    public $fecha_actualizacion;



    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            [['nombre', 'anio', 'semestre', 'guia', 'depto', 'plan', 'caracter', 'fecha_actualizacion'], 'string'],
            ['profesores', 'safe'],
            [['codigo', 'ects'], 'integer'],
        ];
    }

    public function setAttributes($values, $safeOnly = true)
    {
        parent::setAttributes($values, $safeOnly);

        $profesores = [];
        foreach($this->profesores as $i=>$p)
        {
            $teacher = new Teacher();
            $teacher->setAttributes($p);
            if($teacher->validate())
            {
                $profesores[] = $teacher;
            }

        }
        $this->profesores = $profesores;

    }

}