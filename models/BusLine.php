<?php

namespace app\models;

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

    /**
     * [getWaitHumanTime ]
     * @return string retunr wait time in human time mode: En la parada, En 1 minuto, En 2 minutos,  10:00 OR 9:05 ...
     */
    public function getWaitHumanTime()
    {
        $waitTimeMinutes = $this->getWaitMinutes();
        $msg = "";
        switch (true)
        {
            case ($waitTimeMinutes == 0):
                # TODO: mirar si es una parada intermedia (Llegando a la parada )o no (Saliendo de la parada)
                $msg .= "*En la parada*";
                break;
            case ($waitTimeMinutes  <= 60):
                $msg .= "En *$waitTimeMinutes minuto";
                if($waitTimeMinutes > 1)
                    $msg .= "s";
                $msg .= "*";
                break;
            case ($waitTimeMinutes > 60):
                $hours = floor($waitTimeMinutes/60);
                $mins = $waitTimeMinutes%60;
                if (strlen((string)$mins) == 1)
                    $mins = "0$mins";
                $msg .= "A las *$hours:$mins*";
                break;
            default:
                $msg .= "$waitTimeMinutes NO VALIDO";
                break;
        }
        return $msg;
    }

}
