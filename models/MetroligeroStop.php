<?php
/**
 * Created by PhpStorm.
 * User: Diego
 * Date: 23/9/16
 * Time: 21:52
 */

namespace app\models;
use yii\base\Model;
class MetroligeroStop extends Model
{
    public $status;
    public $cached;
    public $data = [];
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
            [['status','cached'], 'string'],
            ['lines', 'each', 'rule'=>['each', 'rule'=>['validateModels']]],
        ];
    }


    public function validateModels($attribute, $value)
    {
        return $this->{$attribute}->validate();
    }

    function setAttributes($values, $safeOnly = true)
    {
        parent::setAttributes($values, $safeOnly);

        $busConnections = new BusConnections;
        $busConnections->setAttributes($this->connectedStops);
        $this->connectedStops = $busConnections;
        $lines = [];
        foreach($this->lines as $i=>$l)
        {
            $line = new BusLine;
            $line->setAttributes($l);
            $lines[$line->lineNumber][] = $line;
        }
        $this->lines = $lines;
    }
}