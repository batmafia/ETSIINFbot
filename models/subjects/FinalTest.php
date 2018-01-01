<?php
/**
 * Created by PhpStorm.
 * User: svg153
 * Date: 3/11/17
 * Time: 18:13
 */

namespace app\models\subjects;

use yii\base\Model;

class FinalTest extends Model
{
    public $actividades = [];

    /*

    */

    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            ['activities', 'safe'],
        ];
    }

    public function setAttributes($values, $safeOnly = true)
    {
        parent::setAttributes($values, $safeOnly);

        $actividades = [];
        foreach($this->evaluacion_continua as $k=>$v)
        {
            $activity = new Activities();
            $activity->setAttributes($v);
            if($activity->validate())
            {
                $actividades[$k] = $activity;
            }

        }
        $this->actividades = $actividades;

    }
}
