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

/*{
    "nombre":"Victor",
    "apellidos":"Gimenez Martinez",
    "email":"victor.gimenez@upm.es",
    "despacho":"1307",
    "coordinador":false,
    "tutorias":[
        {
        "dia":"1",
        "hora_inicio":"13:00",
        "hora_fin":"15:00",
        "observaciones":null
        },
        {
            "dia":"3",
            "hora_inicio":"13:00",
            "hora_fin":"15:00",
            "observaciones":null
        },
        {
            "dia":"4",
            "hora_inicio":"13:00",
            "hora_fin":"15:00",
            "observaciones":null
        }
    ]
},*/

    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            [['nombre','apellidos','email','despacho'], 'string'],
            ['coordinador', 'boolean'],
            ['tutorias', '??????'],
        ];
    }


}
