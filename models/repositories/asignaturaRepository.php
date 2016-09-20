<?php
/**
 * Created by PhpStorm.
 * User: Diego
 * Date: 20/9/16
 * Time: 12:16
 */
namespace app\models\repositories;

use app\models\Asignatura;
use Httpful\Mime;
use Httpful\Request;
use yii\base\Exception;

class AsignaturaRepository
{


    public static function getAsignatura($plan,$codAsignatura,$semestre,$anio)
    {
        $request = Request::get("https://www.upm.es/comun_gauss/publico/api/$anio/$semestre/$plan"."_"."$codAsignatura.json")
            ->expects(Mime::JSON)->send();
        if(!$request->hasErrors())
        {
            $asigObj = new Asignatura();
            $data = \GuzzleHttp\json_decode($request->raw_body, true);
            $asigObj->setAttributes($data);

            if($asigObj->validate())
            {
                return $asigObj;
            }
            else
            {
                print_r($asigObj->getErrors());
            }
        }
        else
        {
            throw new Exception("Repository exception");
        }

    }

}