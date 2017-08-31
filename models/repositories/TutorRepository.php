<?php
/**
 * Created by PhpStorm.
 * User: Diego
 * Date: 10/4/17
 * Time: 21:02
 */

namespace app\models\repositories;

use app\models\tutor\Tutor;
use app\models\tutor\Alumno;
use Exception;
use Httpful\Request;
use Httpful\Mime;
use Sunra\PhpSimple\HtmlDomParser;
use app\models\directory\DirectoryResponse;

class TutorRepository
{

    public static function getTutoria($matricula)
    {

        // return alumno and profesor info
        $tutoria = [];

        if (!self::isValidMat($matricula)){
            return null;
        }

        // https://www.fi.upm.es/index.php?id=consultatutoria
        $urlTutoria="https://www.fi.upm.es/index.php?id=consultatutoria&E_buscar=$matricula";
        // NOTE: Webpage has this:
        //       <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
        //       so, the codification is iso-8859-1
        //       https://stackoverflow.com/a/5173006

        $request = Request::get($urlTutoria)->followRedirects(true)->expects(Mime::HTML)->send();

        if(!$request->hasErrors()) {

            $retTutorInfo = "";

            $dom = HtmlDomParser::str_get_html($request->raw_body);

            // if send $matricula empty return
            if ($dom->find('Debe especificar el número de matrícula completo')) {
                return null;
            }


            //
            // alumno
            //

            $headerAlumno = [];
            $fieldAlumno = [];


            $alumnoDataSTR = $dom->find('h2', 0);
            $alumnoDataSTR = $alumnoDataSTR->next_sibling();
            $alumnoDataSTR = $alumnoDataSTR->innertext;
            $alumnoDataSTR = mb_convert_encoding($alumnoDataSTR, "UTF-8", "ISO-8859-1");

            $wordlistToRemove = array("<b>", "</b>");
            foreach ($wordlistToRemove as $word)
                $alumnoDataSTR = str_replace($word, "", $alumnoDataSTR);

            $alumnoDataARRAY = explode("<br />", $alumnoDataSTR);

            # Alumno does not exist
            if (sizeof($alumnoDataARRAY) == 1 &&
            $alumnoDataARRAY[0] === "<tr><th>Profesor</th> <th>Departamento</th> <th>Despacho</th> <th>Curso</th></tr>") {
                # privacity
                return null;
            }

            foreach ($alumnoDataARRAY as $row) {
                $alumnoDataARRAYRow = explode(": ", $row);
                array_push($headerAlumno, $alumnoDataARRAYRow[0]);
                array_push($fieldAlumno, $alumnoDataARRAYRow[1]);
            }

            // Possible Alumnos names in $profesor:
            // @TODO: make a funtion to generalize this.
            if (strpos($fieldAlumno[0], ", ") !== false) {
                // apellido apellido,nombre
                $explode = explode(", ", $fieldAlumno[0]);
                $nombreAlumno = self::strtolower_utf8($explode[1]);
                $apellidosAlumno = self::strtolower_utf8($explode[0]);
            } elseif (strpos($fieldAlumno[0], ",") !== false) {
                # apellido apellido, nombre
                $explode = explode(",", $fieldAlumno[0]);
                $nombreAlumno = self::strtolower_utf8($explode[1]);
                $apellidosAlumno = self::strtolower_utf8($explode[0]);
            } elseif (substr_count($fieldAlumno[0], " ") == 1) {
                # nombre apellido
                $explode = explode(" ", $fieldAlumno[0]);
                $nombreAlumno = self::strtolower_utf8($explode[0]);
                $apellidosAlumno = self::strtolower_utf8($explode[1]);
            } else {
                # nombre apellido apellido
                $explode = explode(" ", $fieldAlumno[0]);
                $apellidosArray = array_slice($explode, 1, -1);
                $nombreAlumno = self::strtolower_utf8($explode[0]);
                $apellidosAlumno = self::strtolower_utf8(implode(" ", $apellidosArray));
            }


            $nMat = $fieldAlumno[1];
            $cursoEmpieze = $fieldAlumno[2];


            $alumnoModel = \Yii::createObject([
                'class' => Alumno::className(),
                'nombre' => $nombreAlumno,
                'apellidos' => $apellidosAlumno,
                'nMat' => $nMat,
                'cursoEmpieze' => $cursoEmpieze
            ]);

            if ($alumnoModel->validate()) {
                array_push($tutoria, $alumnoModel);
            } else {
                $emptyAlumnoModel = \Yii::createObject([
                    'class' => Alumno::className()]);
                array_push($tutoria, $emptyAlumnoModel);
                print_r($alumnoModel->getErrors());
            }


            //
            // tutor
            //

            $columns = $dom->find('table tr th');
            $data = $dom->find('table tr td');

            $d1 = [];
            $d2 = [];

            foreach($columns as $campos=>$ths)
            {
                $headCol = mb_convert_encoding($ths->innertext, "UTF-8", "ISO-8859-1");
                array_push($d1,$headCol);

            }
            foreach($data as $campos=>$tds)
            {
                // https://stackoverflow.com/a/5173006
                $dataCol = mb_convert_encoding($tds->innertext, "UTF-8", "ISO-8859-1");
                array_push($d2,$dataCol);
            }

            // @TODO: Why??
            // $info = array_combine($d1,$d2);

            if (empty($d2))
            {
                $emptyTutorModel = \Yii::createObject([
                    'class' => Tutor::className()]);
                array_push($tutoria, $emptyTutorModel);
                return $tutoria;
            }


            $profesor = $d2[0];
            $nombre = "";
            $apellidos = "";
            $enlace = "";
            $departamento = $d2[1];
            $despacho = $d2[2];
            $curso = $d2[3];
            $departamento = "";
            $despacho = "";
            $telefono = "";
            $nombreEmail = "";
            $dominioEmail = "";



            // Possible teachers names in $profesor:
            // @TODO: make a funtion to generalize this.
            if (strpos($profesor, ", ") !== false) {
                // apellido apellido,nombre
                $explode = explode(", ", $profesor);
                $nombre = $explode[1];
                $apellidos = $explode[0];
            } elseif (strpos($profesor[0], ",") !== false) {
                # apellido apellido, nombre
                $explode = explode(",", $profesor);
                $nombre = $explode[1];
                $apellidos = $explode[0];
            } elseif (substr_count($profesor, " ") == 1) {
                # nombre apellido
                $explode = explode(" ", $profesor);
                $nombre = self::strtolower_utf8($explode[0]);
                $apellidos = self::strtolower_utf8($explode[1]);
            } else {
                # nombre apellido apellido
                $explode = explode(" ", $profesor);
                $apellidosArray = array_slice($explode, 1, -1);
                $nombre = self::strtolower_utf8($explode[0]);
                $apellidos = self::strtolower_utf8(implode(" ", $apellidosArray));
            }


            // can we get more info about the tutor in teachers directory?
            $tutorFullName = "$nombre $apellidos";

            $directoryMatchesByTutorName = DirectoryRepository::getDirectoryInfo(urlencode($tutorFullName));

            $encontrado = false;
            if ($encontrado == false && sizeof($directoryMatchesByTutorName) == 1)
            {
                $tutorInfoDirectory = $directoryMatchesByTutorName[0];
                $encontrado = true;
            }

            if ($encontrado == false && sizeof($directoryMatchesByTutorName) > 1)
            {
                foreach ($directoryMatchesByTutorName as $possibleTutor)
                {
                    $possibleTutorFullName = $possibleTutor['nombre'] . " ". $possibleTutor['apellidos'];
                    if ($encontrado == false && strpos($possibleTutorFullName, $tutorFullName) !== false)
                    {
                        $tutorInfoDirectory = $possibleTutor;
                        $encontrado = true;
                        break;
                    }
                }
            }



            if ($encontrado == false && sizeof($directoryMatchesByTutorName) == 0)
            {
                // try again but only with the surnames
                $directoryMatchesByTutorName = DirectoryRepository::getDirectoryInfo(urlencode($apellidos));

                if ($encontrado == false && sizeof($directoryMatchesByTutorName) == 1)
                {
                    $tutorInfoDirectory = $directoryMatchesByTutorName[0];
                    $encontrado = true;
                }

                if ($encontrado == false && sizeof($directoryMatchesByTutorName) > 1)
                {
                    foreach ($directoryMatchesByTutorName as $possibleTutor)
                    {
                        // posible the tutor has second name
                        $explode = explode($possibleTutor['nombre'], " ");
                        $possibleTutorFullName = $explode[0] . " ". $possibleTutor['apellidos'];
                        if ($encontrado == false && strpos($possibleTutorFullName, $tutorFullName) !== false)
                        {
                            $tutorInfoDirectory = $possibleTutor;
                            $encontrado = true;
                            break;
                        }
                    }
                }
            }

            if ($encontrado == false && sizeof($directoryMatchesByTutorName) == 0)
            {
                // try again but only with the first surname
                // IGOR BOGUSLAVSKIY MARGOLIN -->  Igor Boguslavskiy [DIA]
                $primerApellidoTutor = explode($apellidos, " ");
                $tutorNameFirstSurname = "$nombre $primerApellidoTutor[0]";

                $directoryMatchesByTutorName = DirectoryRepository::getDirectoryInfo(urlencode($tutorNameFirstSurname));

                if ($encontrado == false && sizeof($directoryMatchesByTutorName) == 1)
                {
                    $tutorInfoDirectory = $directoryMatchesByTutorName[0];
                    $encontrado = true;
                }

                if ($encontrado == false && sizeof($directoryMatchesByTutorName) > 1)
                {
                    foreach ($directoryMatchesByTutorName as $possibleTutor)
                    {
                        // posible the tutor has second name
                        $explode = explode($possibleTutor['nombre'], " ");

                        if ($encontrado == false && strpos($tutorNameFirstSurname, $tutorFullName) !== false)
                        {
                            $tutorInfoDirectory = $possibleTutor;
                            $encontrado = true;
                            break;
                        }
                    }
                }

            }


            if ($encontrado == true && $tutorInfoDirectory !== null && $tutorInfoDirectory !== "")
            {
                $nombre = $tutorInfoDirectory['nombre'];
                $apellidos = $tutorInfoDirectory['apellidos'];

                if ($tutorInfoDirectory['enlace'] !== null && $tutorInfoDirectory['enlace'] !== "")
                {
                    $enlace = $tutorInfoDirectory['enlace'];
                }

                $departamento = $tutorInfoDirectory['departamento'];

                if ($tutorInfoDirectory['despacho'] !== null && $tutorInfoDirectory['despacho'] !== "")
                {
                    $despacho = $tutorInfoDirectory['despacho'];
                }

                $telefono = $tutorInfoDirectory['telefono'];
                $nombreEmail = $tutorInfoDirectory['nombreEmail'];
                $dominioEmail = $tutorInfoDirectory['dominioEmail'];
            }


            $tutorModel = \Yii::createObject([
                'class' => Tutor::className(),
                'nombre' => $nombre,
                'apellidos' => $apellidos,
                'enlace' => $enlace,
                'departamento' => $departamento,
                'despacho'=> $despacho,
                'curso' => $curso,
                'nombreEmail' => $nombreEmail,
                'telefono' => $telefono,
                'dominioEmail' => $dominioEmail
            ]);

            if($tutorModel->validate())
            {
                array_push($tutoria, $tutorModel);
            } else {
                $emptyTutorModel = \Yii::createObject([
                    'class' => Tutor::className()]);
                array_push($tutoria, $emptyTutorModel);
                print_r($tutorModel->getErrors());
            }


            //return $tutorRET;
            return $tutoria;

        }
        else
        {
            throw new Exception("Repository exception");
        }
    }

    public static function getTutor($matricula)
    {
        $tutor = self::getTutoria($matricula)[1];
        return $tutor;
    }

    public static function getAlumno($matricula)
    {
        $alumno = self::getTutoria($matricula)[0];
        return $alumno;
    }


    public static function isValidMat($matricula)
    {
        // matricula valid format
        // length matricula == 6
        // YY -> started year ; X -> [0-9] ->
        //     II = YYXXXX
        //          i.e ==> 170001 -> 17 -> started year, 0001 -> number
        //     MI = YYmXXX
        //          i.e ==> 17m001 -> 17 -> started year, m -> MI,  001 -> number
        //     ADE = YYiXXX
        //          i.e ==> 17m001 -> 17 -> started year, i -> ADE,  001 -> number

        // thanks to @alvarogtx300
        $re = '/^\d{2}[mi\d]\d{3}$/i';

        return preg_match($re, $matricula);
    }

    // http://php.net/manual/en/function.mb-convert-case.php
    function strtolower_utf8($string){
        return mb_convert_case($string, MB_CASE_TITLE, "UTF-8");
    }

}