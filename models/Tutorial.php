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
            [['dia','hora_inicio','hora_fin','observaciones'], 'string'],
        ];
    }


}