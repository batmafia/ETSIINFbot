<?php
/**
 * Created by PhpStorm.
 * User: svg153
 * Date: 14/08/17
 * Time: 16:19
 */

namespace app\models\tutor;


use yii\base\Model;

class Alumno extends Model
{

    public $nombre;
    public $apellidos;
    public $nMat;
    public $cursoEmpieze;

    function rules()
    {
        return [
            [['nombre', 'apellidos', 'nMat', 'cursoEmpieze'], 'string'],
            [['nombre', 'apellidos', 'nMat', 'cursoEmpieze'],'safe']
        ];
    }

}