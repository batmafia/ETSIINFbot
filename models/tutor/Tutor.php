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

    public $profesor;
    public $departamento;
    public $despacho;
    public $curso;

    function rules()
    {
        return [
            [['profesor', 'departamento','despacho','curso'], 'string']
        ];
    }

}