<?php
/**
 * Created by PhpStorm.
 * User: Diego
 * Date: 23/9/16
 * Time: 21:52
 */

namespace app\models\metroligero;
use yii\base\Model;
class MetroligeroApiResponse extends Model
{
    public $status;
    public $cached;
    public $data = [];

    public $dataClass;

/*{
    "status":"OK",
    "cached":true,
    "data":{
        "date":"Fri, 23 Sep 2016 21:34:24 +0200",
        "first_stop":695,
        "second_stop":1474
    }
}*/



    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            ['status', 'string'],
            ['cached','boolean'],
            ['data', 'validateModel'],
        ];
    }


    public function validateModel($attribute, $value)
    {
        return $this->{$attribute}->validate();
    }

    function setAttributes($values, $safeOnly = true)
    {
        parent::setAttributes($values, $safeOnly);

        $data = new $this->dataClass;
        /** @var $data Model */
        $data->setAttributes($this->data);

        $this->data = $data;


    }
}