<?php
/**
 * Created by PhpStorm.
 * User: Diego
 * Date: 22/9/16
 * Time: 15:11
 */

namespace app\models;

use yii\base\Model;

class Tutorial extends Model
{
    public $dia;
    public $hora_inicio;
    public $hora_fin;
    public $observaciones;
    /*{
                "dia":"3",
                "hora_inicio":"13:00",
                "hora_fin":"15:00",
                "observaciones":null
            },

    },*/

    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            [['dia','hora_inicio','hora_fin'], 'string'],
            ['observaciones','safe']
        ];
    }

    public function getTutoriaMessage()
    {
        $days = [
            '1'     =>  'lunes',
            '2'     =>  'martes',
            '3'     =>  'miércoles',
            '4'     =>  'jueves',
            '5'     =>  'viernes'
        ];

        $dayOfWeek = $days[$this->dia];

        $message = "Los $dayOfWeek de $this->hora_inicio a $this->hora_fin";

        if($this->observaciones!==null){
            $message.="\nObservaciones: $this->observaciones";
        }

        return $message;
    }
}