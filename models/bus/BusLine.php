<?php

namespace app\models\bus;

use yii\base\Model;

class BusLine extends Model
{
    public $lineNumber;
    public $source; // string INTERURBAN || EMT
    public $waitTime; // nMin || hh:mm || <<<
    public $lineBound; // string
    public $isNightLine; // true || false


    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            // lineNumber must be an integer value
            ['lineNumber', 'integer'],
            [['source','waitTime','lineBound'], 'string'],
            ['source', 'checkSource'],
            // isNightLine must be a boolean value
            ['isNightLine', 'boolean'],
        ];
    }

    /**
     * @return true if value are EMT or INTERURBAN. false in other case.
     */
    public function checkSource($attribute, $value)
    {
        return ( $value === "EMT" || $value === "INTERURBAN" );
    }

    public function getWaitMinutes()
    {
        $waitTimeAPI = $this->waitTime; // <<< | mins (25 mins) || time (11:25)
        $waitTimeMinutes = -1;
        if($waitTimeAPI == "<<<")
        {
            $waitTimeMinutes = 0;
        }
        if(substr($waitTimeAPI, -3) === 'min')
        {
            // quitamos el espacio del "15 min" -> "15"
            $waitTimeMinutes = intval(substr($waitTimeAPI, 0, -4));
        }
        elseif (strpos($waitTimeAPI, ':') !== false)
        {
            // quitamos el espacio del "10:30" -> 10*60+30 = 630 min
            $time = explode(":", $waitTimeAPI);
            $hours = intval($time[0]);
            $min = intval($time[1]);
            $waitTimeMinutes = $hours*60+$min;
        }
        return $waitTimeMinutes;
    }

}
