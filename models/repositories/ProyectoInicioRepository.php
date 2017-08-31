<?php
/**
 * Created by PhpStorm.
 * User: Diego
 * Date: 10/4/17
 * Time: 21:02
 */

namespace app\models\repositories;


use app\models\proyectoInicio\AlumnoGrupoPI;

use Exception;
use Httpful\Request;
use Httpful\Mime;
use Sunra\PhpSimple\HtmlDomParser;

class ProyectoInicioRepository
{


    public static function getGrupoPI($dni)
    {

        // return alumno and profesor info
        $alumnoGrupoPI = \Yii::createObject([
            'class' => AlumnoGrupoPI::className()]);;

        // https://www.fi.upm.es/index.php?id=piequipos
        $urlGrupoPI="https://www.fi.upm.es/index.php?id=piequipos&E_tipo=s&E_buscar=$dni";
        // NOTE: Webpage has this:
        //       <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
        //       so, the codification is iso-8859-1
        //       https://stackoverflow.com/a/5173006

        $request = Request::get($urlGrupoPI)->followRedirects(true)->expects(Mime::HTML)->send();

        if(!$request->hasErrors()) {

            $dom = HtmlDomParser::str_get_html($request->raw_body);



            // if dni is not valid
            $domSTR = $dom->find('h2', 0);
            $domSTR = $domSTR->innertext;
            $domSTR = mb_convert_encoding($domSTR, "UTF-8", "ISO-8859-1");
            if ($domSTR !== 'Datos del alumno') {
                return null;
            }


            //
            // alumno
            //

            $headerAlumno = [];
            $fieldAlumno = [];


            $alumnoData = $dom->find('h2', 0);
            $alumnoDataS = $alumnoData->next_sibling();
            $alumnoDataSTR = $alumnoDataS->innertext;
            $alumnoDataSTR = mb_convert_encoding($alumnoDataSTR, "UTF-8", "ISO-8859-1");

            $wordlistToRemove = array("<b>", "</b>");
            foreach ($wordlistToRemove as $word)
                $alumnoDataSTR = str_replace($word, "", $alumnoDataSTR);

            $alumnoDataARRAY = explode("<br />", $alumnoDataSTR);

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

            $alumnoData = $dom->find('div[class=contenido]', 0);
            $correoUPM_dom_STR = $alumnoData->innertext;
            $correoUPM_dom_STR = mb_convert_encoding($correoUPM_dom_STR, "UTF-8", "ISO-8859-1");
            $correoUPM_STR = explode('Correo UPM:', $correoUPM_dom_STR);
            $correoUPM_STR = explode('</p>', $correoUPM_STR[1]);
            $str = $correoUPM_STR[0];
            foreach ($wordlistToRemove as $word)
                $str = str_replace($word, "", $str);
            $correoUPM = substr($str, 1);




            //
            // grupo
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
                $alumnoGrupoPI = \Yii::createObject([
                    'class' => AlumnoGrupoPI::className(),
                    'nombre' => $nombreAlumno,
                    'apellidos' => $apellidosAlumno,
                    'nMat' => $nMat,
                    'cursoEmpieze' => $cursoEmpieze,
                    'correoUPM' => $correoUPM
                ]);
                if (! $alumnoGrupoPI->validate()) {
                    print_r($alumnoGrupoPI->getErrors());
                    $alumnoGrupoPI = \Yii::createObject([
                        'class' => AlumnoGrupoPI::className()]);
                }
                return $alumnoGrupoPI;
            }

            // 'plan', 'equipoPI', 'turno'
            $plan = $d2[0];
            $equipoPI = $d2[1];
            $turno = $d2[2];

            $alumnoData = $dom->find('div[class=contenido]', 0);
            $correoUPM_dom_STR = $alumnoData->innertext;
            $turnoMsg_dom_STR = explode('<p class=\'aviso\'>', $correoUPM_dom_STR);
            $turnoMsg_dom_STR = explode('</p><div><br /></div>    <br />', $turnoMsg_dom_STR[1]);
            $str = $turnoMsg_dom_STR[0];
            foreach ($wordlistToRemove as $word)
                $str = str_replace($word, "", $str);
            $turnoMsg = $str;

            $horaTurno = '9';
            if ($turno == 'B') {
                $horaTurno = '10';
            }


            $alumnoGrupoPI = \Yii::createObject([
                'class' => AlumnoGrupoPI::className(),
                'nombre' => $nombreAlumno,
                'apellidos' => $apellidosAlumno,
                'nMat' => $nMat,
                'cursoEmpieze' => $cursoEmpieze,
                'correoUPM' => $correoUPM,
                'plan' => $plan,
                'equipoPI' => $equipoPI,
                'turno' => $turno,
                'turnoMsg' => $turnoMsg,
                'horaTurno' => $horaTurno
            ]);
            if (! $alumnoGrupoPI->validate()) {
                print_r($alumnoGrupoPI->getErrors());
                $alumnoGrupoPI = \Yii::createObject([
                    'class' => AlumnoGrupoPI::className()]);
            }


            return $alumnoGrupoPI;

        }
        else
        {
            throw new Exception("Repository exception");
        }
    }


    // http://php.net/manual/en/function.mb-convert-case.php
    function strtolower_utf8($string){
        return mb_convert_case($string, MB_CASE_TITLE, "UTF-8");
    }


}
