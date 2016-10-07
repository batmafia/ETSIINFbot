<?php
/**
 * Created by PhpStorm.
 * User: Diego
 * Date: 30/9/16
 * Time: 11:54
 */

namespace app\models;

use yii\base\Model;

class PlanEnrollment extends Model
{
    public $codigo_grupo;
    public $nombre_grupo;
    public $idioma;


/*
 * codigo_grupo : "4F3T"
 * nombre_grupo : "GRUPO CUARTO SEMESTRE - SEGUNDO CURSO"
 * idioma : "CAS"
*/

    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            [['codigo_grupo','nombre_grupo','idioma'], 'string'],
        ];
    }

}