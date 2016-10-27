<?php

namespace app\models\repositories;

use app\models\bus\BusStop;
use app\models\bus\;
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
        if(!$request->hasErrors())
        {
            $busObj = new BusStop();
            $data = \GuzzleHttp\json_decode($request->raw_body, true);
            $busObj->setAttributes($data);

            if($busObj->validate())
            {
                return $busObj;
            }
            else
            {
                print_r($busObj->getErrors());
            }
        }
        else
        {
            throw new Exception("Repository exception");
        }

    }

    /**
     * @return
     */
    public static function getFullTimeBuses()
    {
        $request = Request::get("http://www.etsiinf.upm.es/apps/autobuses/v2/")->expects(Mime::JSON)->send();
        if(!$request->hasErrors())
        {
            $FullTimeBusesObj = new FullTimeBusesBusLines();
            $data = \GuzzleHttp\json_decode($request->raw_body, true);
            $FullTimeBusesObj->setAttributes($data);

            if($FullTimeBusesObj->validate())
            {
                return $FullTimeBusesObj;
            }
            else
            {
                print_r($FullTimeBusesObj->getErrors());
            }
        }
        else
        {
            throw new Exception("Repository exception");
        }

    }

}
