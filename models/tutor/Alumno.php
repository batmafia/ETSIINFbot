<?php
/**
 * Created by PhpStorm.
 * User: svg153
 * Date: 14/08/17
 * Time: 16:19
 */

namespace app\models\alumno;


use yii\base\Model;

class Alumno extends Model
{

    public $nombre;
    public $apellidos;
    public $nMat;
    public $curso;

    function rules()
    {
        return [
            [['nombre', 'apellidos','nMat','curso'], 'string'],
            [['nombre', 'apellidos','nMat','curso'],'safe']
        ];
    }

}