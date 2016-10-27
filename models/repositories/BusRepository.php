<?php

namespace app\models\repositories;

use app\models\bus\BusStop;
use app\models\bus\fullTimeBuses;
use Httpful\Mime;
use Httpful\Request;
use yii\base\Exception;

class BusRepository
{

    /**
     * @param $busStopId
     * @return BusStop
     */
    public static function getBusStop($busStopId)
    {
        $request = Request::get("http://api.interurbanos.welbits.com/v1/stop/$busStopId")->expects(Mime::JSON)->send();
        if (!$request->hasErrors()) {
            $busObj = new BusStop();
            $data = \GuzzleHttp\json_decode($request->raw_body, true);
            $busObj->setAttributes($data);

            if ($busObj->validate()) {
                return $busObj;
            } else {
                print_r($busObj->getErrors());
            }
        } else {
            throw new Exception("Repository exception");
        }

    }

    /**
     * @return
     */
    public function getFullTimeBuses()
    {
        $request = Request::get("http://www.etsiinf.upm.es/apps/autobuses/v2/")->expects(Mime::JSON)->send();
        if (!$request->hasErrors()) {
            $data = \GuzzleHttp\json_decode($request->raw_body, true);

            $availableLines=[];
            foreach ($data as $key => $line)
            {
               foreach ($line as $k => $lineObject){
                   $myLine = new fullTimeBuses\FullTimeBusesLine();
                   $myLine->setAttributes($lineObject);

                   if ($myLine->validate()) {
                       $availableLines[$lineObject['idLinea']]=$myLine;
                   } else {
                       print_r($myLine->getErrors());
                   }
               }

            }

        }
        else
        {
            throw new Exception("Repository exception");
        }
        return $availableLines;
    }

    public static function getFullTimeBusesOpts($idLine, $origin)
    {

        $request = Request::get("http://www.etsiinf.upm.es/apps/autobuses/v2/")->expects(Mime::JSON)->send();
        if (!$request->hasErrors()) {
            $data = \GuzzleHttp\json_decode($request->raw_body, true);

            $availableLines=[];
            foreach ($data as $key => $line)
            {
                foreach ($line as $k => $lineObject){
                    $myLine = new fullTimeBuses\FullTimeBusesLine();
                    $myLine->setAttributes($lineObject);

                    if ($myLine->validate()) {
                        $availableLines[$lineObject['idLinea']]=$myLine;
                    } else {
                        print_r($myLine->getErrors());
                    }
                }

            }

        }
        else
        {
            throw new Exception("Repository exception");
        }

        $dayType = "";
        if ($idLine==='591' || $idLine==='865') {
            $dayType = 'lectivo';
        }
        if ($idLine==='571') {
            $dayType = 'laboral';
        }
        if ($idLine==='573') {
            $dayType = 'laborsabado';
        }


        $dest = "";
        if ($idLine==='591' && $origin==='Madrid') {
            $dest = 'Aluche >> ETSIINF';
        }
        if ($idLine==='591' && $origin==='ETSIINF') {
            $dest = 'ETSIINF >> Aluche';
        }
        if ($idLine==='865' && $origin==='Madrid') {
            $dest = 'C.Universitaria >> ETSIINF';
        }
        if ($idLine==='865' && $origin==='ETSIINF') {
            $dest = 'ETSIINF >> C.Universitaria';
        }
        if ($idLine==='571' && $origin==='Madrid') {
            $dest = 'Aluche >> Boadilla';
        }
        if ($idLine==='571' && $origin==='ETSIINF') {
            $dest = 'Boadilla >> Aluche';
        }
        if ($idLine==='573' && $origin==='Madrid') {
            $dest = 'Moncloa >> Boadilla';
        }
        if ($idLine==='573' && $origin==='ETSIINF') {
            $dest = 'Boadilla >> Moncloa';
        }

        $dw = idate("w", time());
        $dayWeekNumber = ($dw===0)?7:$dw;

        $hours = [];

        foreach ($availableLines[$idLine]['periodos'][$dayType]['horarios'][$dest]['listadoHoras'] as $key => $value)
        {
            if (strpos($key, "$dayWeekNumber") !== FALSE)
            {
                $hours = array_merge( $hours, $value['horas']);
            }
        }

        //echo $availableLines[$idLine]['periodos'][$dayType]['horarios'][$dest]['listadoHoras']['12345']['horas'][0];

        return $hours;

    }


}
