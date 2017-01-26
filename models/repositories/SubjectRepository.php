<?php
/**
 * Created by PhpStorm.
 * User: Diego
 * Date: 20/9/16
 * Time: 12:16
 */
namespace app\models\repositories;

use app\models\subjects\CenterPlan;
use app\models\subjects\PlanSubject;
use app\models\subjects\Subject;
use Httpful\Mime;
use Httpful\Request;
use yii\base\Exception;

class SubjectRepository
{


    public static function getSubject($plan, $codSubject, $semester, $year)
    {
        $year2 = substr($year + 1, -2);
        $request = Request::get("https://www.upm.es/comun_gauss/publico/api/$year-$year2/$semester/$plan" . "_" . "$codSubject.json")
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

    /**
     * @param $codeSubject
     * @return Subject
     * @throws Exception
     */
    public static function getSubjectByCode($codeSubject)
    {
        $year = self::getActualYear();
        $year2 = substr($year + 1, -2);
        // ETSIINF = 10; PSC = Primer y Segundo Ciclo; GRA = Grado
        $plans = SubjectRepository::getPlansFromCenter('10','PSC','GRA,MOF',$year);

        foreach ( $plans as $plan )
        {
            $subjectsOrderedList = SubjectRepository::getSubjectsList($plan->codigo, $year);
            foreach ( $subjectsOrderedList as $course )
            {
                foreach ( $course as $semester )
                {

                    foreach ( $semester as $subject )
                    {

                        // if (strcmp($subject->codigo, $codeSubject))
                        if ($subject->codigo == $codeSubject)
                        {
                            $semesterCode = array_search($semester,$course); // $semesterCode = $subject->imparticion[0]->codigo_duracion;
                            $planCode = $plan->codigo;

                            $request = Request::get("https://www.upm.es/comun_gauss/publico/api/$year-$year2/$semesterCode/$planCode" . "_" . "$codeSubject.json")
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
                    }
                }
            }
        }



    }


    public static function getPlansFromCenter($center, $studyType, $studySubType, $year)
    {
        $year2 = substr($year + 1, -2);
        $request = Request::get("https://www.upm.es/wapi_upm/academico/comun/index.upm/v2/centro.json/$center/planes/$studyType?subtipo_estudio=$studySubType&anio=$year$year2")
            ->expects(Mime::JSON)->send();
        if (!$request->hasErrors()) {

            $data = \GuzzleHttp\json_decode($request->raw_body, true);
            $availablePlans = [];

            foreach ($data as $plan)
            {

                $myplan = new CenterPlan();
                $myplan->setAttributes($plan);

                if ($myplan->validate()) {
                    $availablePlans[]=$myplan;
                } else {
                    print_r($plan->getErrors());
                }
            }

            return $availablePlans;

        } else {
            throw new Exception("Repository exception");
        }

    }



    public static function getSubjectsList($plan, $year)
    {
        $year2 = substr($year + 1, -2);
        $request = Request::get
        ("https://www.upm.es/wapi_upm/academico/comun/index.upm/v2/plan.json/$plan/asignaturas?anio=$year$year2")
            ->expects(Mime::JSON)->send();
        if (!$request->hasErrors()) {

            $data = \GuzzleHttp\json_decode($request->raw_body, true);
            $subjectsList=[];

            foreach ($data as $subjCode => $subject)
            {
                $subject2 = new PlanSubject();
                $subject2->setAttributes($subject);

                if ($subject2->validate()) {
                    foreach ($subject2->imparticion as $impartition) {
                        $subjectsList[" ".$subject2->curso][$impartition->codigo_duracion][$subject2->nombre] = $subject2;
                    }
                }
                else
                {
                    print_r($subject->getErrors());
                }

            }

            ksort($subjectsList);
            foreach ($subjectsList as $i=>$course)
            {
                ksort($subjectsList[$i]);
            }

            return $subjectsList;

        } else {
            throw new Exception("Repository exception");
        }
    }

    public static function getSubjectsMachedByText($subjectName)
    {
        $actualYear = self::getActualYear();
        $subjectNameProcessed = self::filterSubjectName($subjectName);
        // ETSIINF = 10; PSC = Primer y Segundo Ciclo; GRA = Grado
        $plans = SubjectRepository::getPlansFromCenter('10','PSC','GRA,MOF',$actualYear);

        foreach ( $plans as $plan )
        {
            $subjectsOrderedList = SubjectRepository::getSubjectsList($plan->codigo, $actualYear);
            foreach ( $subjectsOrderedList as $course )
            {
                foreach ( $course as $semester )
                {
                    foreach ( $semester as $subject)
                    {
                        $nombreProcesado = self::filterSubjectName($subject->nombre);
                        if (strpos($nombreProcesado, $subjectNameProcessed) !== false)
                        {
                            $subjectsOrderedMachedList[$subject->codigo] = $subject;
                        }
                    }
                }
            }
        }

        return $subjectsOrderedMachedList;
    }

    public static function getGuide($planImpartition){

        $request = Request::get($planImpartition->guia_pdf)->withoutStrictSSL()->send();
        if (!$request->hasErrors()) {
            $data = $request->raw_body;
            return $data;

        } else {
            throw new Exception("Repository exception");
        }

    }

    public static function getActualYear()
    {
        $year = intval(date("Y"));

        if (intval(date("m")) <= 7)
            $year--;

        return $year;
    }

    private static function filterSubjectName($subjectName)
    {
        return strtolower(self::tirarAcentos($subjectName));
    }

    private static function tirarAcentos($string)
    {
        return preg_replace(array("/(á|à|ã|â|ä)/","/(Á|À|Ã|Â|Ä)/","/(é|è|ê|ë)/","/(É|È|Ê|Ë)/","/(í|ì|î|ï)/","/(Í|Ì|Î|Ï)/","/(ó|ò|õ|ô|ö)/","/(Ó|Ò|Õ|Ô|Ö)/","/(ú|ù|û|ü)/","/(Ú|Ù|Û|Ü)/","/(ñ)/","/(Ñ)/"),explode(" ","a A e E i I o O u U n N"),$string);
    }

}