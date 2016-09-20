<?php
/**
 * Created by PhpStorm.
 * User: Diego
 * Date: 20/9/16
 * Time: 12:32
 */

namespace app\models;

use yii\base\Model;

class Teacher extends Model
{
    public $nombre;
    public $apellidos;
    public $email;
    public $despacho;
    public $coordinador;
    public $tutorias = [];

// {
    // "nombre":"Maria Gloria",
    // "apellidos":"Sanchez Torrubia",
    // "email":"mariagloria.sanchez@upm.es",
    // "despacho":"1318",
    // "coordinador":false,
    // "tutorias":[
    // {
    // "dia":null,
    // "hora_inicio":null,
    // "hora_fin":null,
    // "observaciones":null
    // }
    // ]
// },

    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            [['nombre','apellidos','email'], 'string'],
            ['despacho', 'integer'],
            ['coordinador', 'boolean'],
            ['tutorias', '??????'],
        ];
    }


}
