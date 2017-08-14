<?php
/**
 * Created by PhpStorm.
 * User: Diego
 * Date: 10/4/17
 * Time: 21:02
 */

namespace app\models\repositories;

use app\models\tutor\Tutor;
use Exception;
use Httpful\Request;
use Httpful\Mime;
use Sunra\PhpSimple\HtmlDomParser;

class TutorRepository
{
    public static function getTutor($matricula)
    {

        $tutoria = [];

/*
        if (!self::isValidMat($matricula)){
            return null;
        }
*/

        $request = Request::get("https://www.fi.upm.es/index.php?id=consultatutoria&E_buscar=$matricula")
            ->followRedirects(true)->expects(Mime::HTML)->send();

        if(!$request->hasErrors())
        {

            $dom = HtmlDomParser::str_get_html($request->raw_body);

            // if send $matricula empty return
            if ($dom->find('Debe especificar el número de matrícula completo')){
                return null;
            }

            /*
            //
            // alumno
            //
            // @TODO: take alumno info from web
            $d0 = [];

            // @TODO: change p by the correct
            $alumnoData= $dom->find('p');

            foreach($alumnoData as $campos=>$ths)
            {
                array_push($d0,$ths->innertext);

            }

            $explode = explode(", ", $d0[0]);
            $nombre = $explode[1];
            $apellidos = $explode[0];

            $alumnoModel = \Yii::createObject([
                'class' => Alumno::className(),
                'nombre' => $nombre,
                'apellidos' => $apellidos,
                '$nMat' => $d0[1],
                'curso' => $d0[2]
            ]);

            if($alumnoModel->validate())
            {
                $tutoria[] = $alumnoModel;
            }

            */


            //
            // tutor
            //

            $columns = $dom->find('table tr th');
            $data = $dom->find('table tr td');

            $d1 = [];
            $d2 = [];

            foreach($columns as $campos=>$ths)
            {
                array_push($d1,$ths->innertext);

            }
            foreach($data as $campos=>$tds)
            {
                array_push($d2,$tds->innertext);
            }

            // @TODO: Why??
            // $info = array_combine($d1,$d2);

            if (empty($d2))
            {
                return $tutorModel = \Yii::createObject([
                    'class' => Tutor::className()]);
            }

            // [profesor] => Robles Santamarta, Juan
            $profesor = $d2[0];
            if (strpos($profesor , ',') !== false){
                $explode = explode(", ", $profesor);
                $nombre = $explode[1];
                $apellidos = $explode[0];
            } else {
                $explode = explode(" ", $profesor);
                $nombre = $explode[0];
                $apellidos = "$explode[1] $explode[2]";
            }

            $tutorModel = \Yii::createObject([
                'class' => Tutor::className(),
                'nombre' => $nombre,
                'apellidos' => $apellidos,
                'departamento' => $d2[1],
                'despacho'=> $d2[2],
                'curso' => $d2[3]
            ]);

            if($tutorModel->validate())
            {
                $tutoria[] = $tutorModel;

            }

            // @TODO: FUTURE: return alumno and profesor info,
            // return $tutoria;

            return $tutorModel;

        }
        else
        {
            throw new Exception("Repository exception");
        }
    }

    public static function isValidMat($matricula)
    {
        // YY -> started year ; X -> [0-9] ->
        //     II = YYXXXX -> 170001 -> 17 -> started year, 0001 -> number
        //     MI = YYmXXX -> 17m001 -> 17 -> started year, m -> MI,  001 -> number
        //     ADE = YYiXXX -> 17m001 -> 17 -> started year, i -> ADE,  001 -> number


        // $re = '/^([0-9]{2})([0-9mi])([0-9]{3})/';

        // return preg_match($re, $matricula);

        $arr = str_split($matricula);

        $valid = is_int(intval($arr[0])) && is_int(intval($arr[1])) &&
            (is_int(intval($arr[2])) || $arr[2] == 'm' || $arr[2] == 'i') &&
            is_int(intval($arr[3])) && is_int(intval($arr[4])) && is_int(intval($arr[5]));

        return $valid;

    }

}