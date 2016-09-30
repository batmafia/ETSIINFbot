<?php
/**
 * Created by PhpStorm.
 * User: Diego
 * Date: 30/9/16
 * Time: 11:19
 */

namespace app\models;

use yii\base\Model;

class PlanSubject extends Model
{

    public $codigo;
    public $nombre;
    public $nombre_ingles;
    public $curso;
    public $codigo_tipo_asignatura;
    public $nombre_tipo_asignatura;
    public $credects;
    public $departamentos = [];
    public $idiomas = [];
    public $imparticion = [];


    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            [['codigo','nombre','nombre_ingles','curso','codigo_tipo_asignatura','nombre_tipo_asignatura','credects'], 'string'],
            ['idiomas', 'each', 'rule'=>['string']],
            [['imparticion','departamentos'], 'each', 'rule'=>['validateModels']],
        ];
    }

    public function validateModels($attribute, $value)
    {
        return $this->{$attribute}->validate();
    }

    public function setAttributes($values, $safeOnly = true)
    {
        parent::setAttributes($values, $safeOnly);

        $departments = [];
        foreach($this->departamentos as $i=>$d)
        {
            $department = new PlanDepartment();
            $department->setAttributes($d);
            $departments[]=$department;
        }
        $this->departamentos = $departments;

        $imparticiones = [];
        foreach($this->imparticion as $i=>$im)
        {
            $impartition = new PlanImpartition();
            $impartition->setAttributes($im);
            $imparticiones[]=$impartition;
        }
        $this->imparticion = $imparticiones;

    }

}