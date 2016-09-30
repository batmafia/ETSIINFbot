<?php
/**
 * Created by PhpStorm.
 * User: Diego
 * Date: 20/9/16
 * Time: 12:16
 */
namespace app\models\repositories;

use app\models\PlanSubject;
use app\models\Subject;
use Httpful\Mime;
use Httpful\Request;
use yii\base\Exception;

class SubjectRepository
{


    public static function getSubject($plan, $codSubject, $semester, $year)
    {
        $request = Request::get("https://www.upm.es/comun_gauss/publico/api/$year/$semester/$plan" . "_" . "$codSubject.json")
            ->expects(Mime::JSON)->send();
        if (!$request->hasErrors()) {
            $subjObj = new Subject();
            $data = \GuzzleHttp\json_decode($request->raw_body, true);
            $subjObj->setAttributes($data);

            if ($subjObj->validate()) {
                return $subjObj;
            } else {
                print_r($subjObj->getErrors());
            }
        } else {
            throw new Exception("Repository exception");
        }

    }

    public static function getSubjectsList($plan, $anio)
    {
        $request = Request::get
        ("https://www.upm.es/wapi_upm/academico/comun/index.upm/v2/plan.json/$plan/asignaturas?anio=$anio")
            ->expects(Mime::JSON)->send();
        if (!$request->hasErrors()) {

            $data = \GuzzleHttp\json_decode($request->raw_body, true);
            $subjectsList=[];

            foreach ($data as $subjCode => $subject)
            {
                $subject2 = new PlanSubject();
                $subject2->setAttributes($subject);

                if ($subject2->validate()) {
                    $subjectsList[]=$subject2;
                }
                else
                {
                    print_r($subject->getErrors());
                }

            }

            return $subjectsList;

        } else {
            throw new Exception("Repository exception");
        }
    }
}