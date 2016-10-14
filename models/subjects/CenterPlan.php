<?php
/**
 * Created by PhpStorm.
 * User: Diego
 * Date: 23/9/16
 * Time: 17:42
 */

namespace app\models\subjects;

use yii\base\Model;

class CenterPlan extends Model
{

    public $codigo;
    public $nombre;
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
            ['nombre', 'filter', 'filter' => function($name)
            {
                return mb_convert_case($name, MB_CASE_TITLE, "UTF-8");
            }],
            [['codigo', 'nombre', 'anio_inicio', 'tipo_estudio', 'subtipo_estudio'], 'string'],
            ['asignaturas', 'url'],
        ];
    }
}