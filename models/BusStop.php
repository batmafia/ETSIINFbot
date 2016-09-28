<?php

namespace app\models;

use yii\base\Model;

class BusStop extends Model
{
    // http://api.interurbanos.welbits.com/v1/stop/8411
    // JSON formater: http://jsonviewer.stack.hu/
    public $stopName; // string
    public $stopType; // string INTERURBAN || EMT
    public $notAvailableLines;
        // string "Actualmente no hay datos para las l\u00edneas: 561A, 591",
    public $connectedStops; // see BusConnections.php
    public $lines = []; // see BusLine.php
    public $stopNumber; // int


    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            [['stopName', 'stopType', 'stopNumber', 'notAvailableLines'], 'string'],
            ['stopType', 'validateStopType'],
            [['connectedStops'], 'validateModels'],
            ['lines', 'each', 'rule'=>['each', 'rule'=>['validateModels']]],
            // stopNumber must be a int
            ['stopNumber', 'integer'],
            [['connectedStops', 'notAvailableLines'], 'safe'],
        ];
    }

    /**
     * @return BusLine
     */
    public function getLinesByNumber($line)
    {
        if(isset($this->lines[$line]))
            return $this->lines[$line];
        return [];
    }


    /**
     * @return true if value are EMT or INTERURBAN. false in other case.
     */
    public function validateStopType($attribute, $value)
    {
        return ( $value === "EMT" || $value === "INTERURBAN" );
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
            // save by $line->waitTime to skip when the api duplicate the time (sometimes)
            $lines[$line->lineNumber][$line->waitTime] = $line;
        }
        $this->lines = $lines;
    }

    function isLineInNotAvailableLines($line)
    {
        $notAvailableLinesArray = getNotAvailableLines();
        return in_array($line, $notAvailableLinesArray);
    }

    function getNotAvailableLines()
    {
        $notAvailableLinesArray = [];
        $str = $this->notAvailableLines;
        if ($str != "") {
            $strLines = explode(": ", $str, 2);
            $strLineWithComas = explode(": ", $strLines[1], 2);
            $notAvailableLinesArray = explode(": ", $strLineWithComas);
        }
        return $notAvailableLinesArray;
    }

}
