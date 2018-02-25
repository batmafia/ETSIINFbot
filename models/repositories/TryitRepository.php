<?php
/**
 * Created by PhpStorm.
 * User: Sergio
 * Date: 25/2/17
 * Time: 12:29
 */
namespace app\models\repositories;

use app\models\tryit\Editions;
use app\models\tryit\Session;
use app\models\tryit\Sessions;
use app\models\tryit\Yearsessions;
use Httpful\Mime;
use Httpful\Request;
use yii\base\Exception;

class TryitRepository
{

    public static function getYearsession($text)
    {

        $request = Request::get("https://congresotryit.es/editions-api/yearsessions/?format=json")
            ->expects(Mime::JSON)->send();
        if (!$request->hasErrors()) {

            $data = \GuzzleHttp\json_decode($request->raw_body, true);

            $yearsessions = [];

            foreach ($data as $y){
                if ($y !== null){

                    foreach ($y as $s){

                        $session = new Yearsession();
                        $session->setAttributes($s);

                        if ($session->validate()) {
                            $yearsessions[]=$session;
                        } else {
                            print_r($session->getErrors());
                        }
                    }
                }

                return $yearsessions;
            }


        } else {
            throw new Exception("Repository exception");
        }
    }


    public static function getEditions($text)
    {

        $request = Request::get("https://congresotryit.es/editions-api/edtions/?format=json")
            ->expects(Mime::JSON)->send();
        if (!$request->hasErrors()) {

            $data = \GuzzleHttp\json_decode($request->raw_body, true);

            $editons = [];

            foreach ($data as $y){
                if ($y !== null){

                    foreach ($y as $s){

                        $session = new Edition();
                        $session->setAttributes($s);

                        if ($session->validate()) {
                            $yearsessions[]=$session;
                        } else {
                            print_r($session->getErrors());
                        }
                    }
                }

                return $editons;
            }


        } else {
            throw new Exception("Repository exception");
        }
    }

    public static function getSessions($text)
    {

        $request = Request::get("https://congresotryit.es/editions-api/sessions/?format=json")
            ->expects(Mime::JSON)->send();
        if (!$request->hasErrors()) {

            $data = \GuzzleHttp\json_decode($request->raw_body, true);

            $editons = [];

            foreach ($data as $y){
                if ($y !== null){

                    foreach ($y as $s){

                        $session = new Session();
                        $session->setAttributes($s);

                        if ($session->validate()) {
                            $yearsessions[]=$session;
                        } else {
                            print_r($session->getErrors());
                        }
                    }
                }

                return $editons;
            }


        } else {
            throw new Exception("Repository exception");
        }
    }

}