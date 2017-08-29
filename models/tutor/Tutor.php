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
    public $enlace;
    public $departamento;
    public $despacho;
    public $curso;
    public $telefono;
    public $nombreEmail;
    public $dominioEmail;


    function rules()
    {
        return [
            [['nombre', 'apellidos', 'enlace', 'departamento', 'despacho', 'curso', 'telefono', 'nombreEmail', 'dominioEmail'], 'string'],
            [['nombre', 'apellidos', 'enlace', 'departamento', 'despacho', 'curso', 'telefono', 'nombreEmail', 'dominioEmail'], 'safe'],
        ];
    }

}