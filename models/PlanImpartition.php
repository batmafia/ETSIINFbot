<?php
/**
 * Created by PhpStorm.
 * User: Diego
 * Date: 30/9/16
 * Time: 11:50
 */

namespace app\models;

use yii\base\Model;

class PlanImpartition extends Model
{
    public $codigo_duracion;
    public $nombre_duracion;
    public $guia_pdf;
    public $guia_json;
    public $grupos_matricula = [];

    /*
     * codigo_duracion : "2S"
    nombre_duracion : "Segundo Semestre"
    guia_pdf : "https://www.upm.es/comun_gauss/publico/guias/2016-17/2S/GA_10II_105000154_2S_2016-17.pdf"
    guia_json : "https://www.upm.es/comun_gauss/publico/api/2016-17/2S/10II_105000154.json"
    grupos_matricula : {}
     */

    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            [['codigo_duracion','nombre_duracion'], 'string'],
            [['guia_pdf','guia_json'],'url'],
            ['grupos_matricula', 'each', 'rule'=>['validateModels']],
        ];
    }

    public function validateModels($attribute, $value)
    {
        return $this->{$attribute}->validate();
    }

    public function setAttributes($values, $safeOnly = true)
    {
        parent::setAttributes($values, $safeOnly);

        $enrollments = [];
        foreach($this->grupos_matricula as $i=>$e)
        {
            $enrollment = new PlanEnrollment();
            $enrollment->setAttributes($e);
            $enrollments[]=$enrollment;
        }
        $this->grupos_matricula = $enrollments;

    }
}