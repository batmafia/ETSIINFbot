<?php
/**
 * Created by PhpStorm.
 * User: Diego
 * Date: 23/9/16
 * Time: 17:42
 */

namespace app\models;

use yii\base\Model;

class CenterPlan extends Model
{

    public $codigo;
    public $nombre;
    public $ects;
    public $anio_inicio;
    public $tipo_estudio;
    public $subtipo_estudio;
    public $asignaturas;



    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            [['codigo', 'nombre', 'anio_inicio', 'tipo_estudio', 'subtipo_estudio'], 'string'],
            ['asignaturas', 'url'],
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