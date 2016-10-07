<?php
/**
 * Created by PhpStorm.
 * User: Diego
 * Date: 30/9/16
 * Time: 11:47
 */

namespace app\models;

use yii\base\Model;

class PlanDepartment extends Model
{
    public $codigo_departamento;
    public $responsable;

    /*
     * codigo_departamento : "D400"
     * responsable : "S"
     */

    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            [['codigo_departamento', 'responsable'], 'string'],
        ];
    }
}