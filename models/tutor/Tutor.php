<?php
/**
 * Created by PhpStorm.
 * User: Diego
 * Date: 11/04/17
 * Time: 10:43
 */

namespace app\models\tutor;


use yii\base\Model;

class Tutor extends Model
{

    public $nombre;
    public $apellidos;
    public $departamento;
    public $despacho;
    public $curso;

    function rules()
    {
        return [
            [['nombre', 'apellidos', 'departamento','despacho','curso'], 'string'],
            [['nombre', 'apellidos', 'departamento','despacho','curso'],'safe']
        ];
    }

}