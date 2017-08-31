<?php
/**
 * Created by PhpStorm.
 * User: Sergio
 * Date: 08/30/17
 * Time: 20:46
 */

namespace app\commands\admin;
use app\commands\base\Request;
use app\commands\base\BaseUserCommand;
use app\models\repositories\TutorRepository;

/**
 * User "/tutorALL" command
 */
class TutorALLCommand extends BaseUserCommand
{
    /**
     * {@inheritdoc}
     */
    public $enabled = true;
    protected $name = 'tutorALL';
    protected $description = 'Devuelve toda la info de los tutorados y tutores.';
    protected $usage = '/tutorALL';
    protected $version = '1.0.0';
    protected $need_mysql = true;


    const CANCELAR = 'Cancelar';


    public function processGetTextForSearch($text)
    {
        $this->getConversation();

        $this->getRequest()->sendAction(Request::ACTION_TYPING);

        $keyboard [] = [self::CANCELAR];

        if ($this->isProcessed() || empty($text))
        {
            return $this->getRequest()->markdown()->keyboard($keyboard)
                ->sendMessage("Introduce el año de cominezo (2009, 1997, 2011 etc...) por el cual quieres comenzar la búsqueda.");
        }
        if ($text === self::CANCELAR)
        {
            return $this->cancelConversation();
        }

        $this->getConversation()->notes['text'] = $text;
        return $this->nextStep();
    }


    public function processGetAllTutories($text)
    {

        if ($text === self::CANCELAR)
        {
            return $this->cancelConversation();
        }

        $this->getRequest()->sendAction(Request::ACTION_TYPING);


        $anio_imput_str = $this->getConversation()->notes['text'];
        if (is_int($anio_imput_str) === false)
        {
            return $this->previousStep();
        }

        // pasamos de 2009 a 09, y luego a valor int -> 9
        $anio_imput_str_cut = substr($anio_imput_str, -2);
        $anio_imput_int = intval($anio_imput_str_cut);


        // header
        $mensaje = "AlumnoNumeroMatricula;AlumnoNombre;AlumnoApellidos;AlumnoCursoEmpieze;TutorNombre;TutorApellidos;TutorEnlace;TutorDepartamento;TutorDespacho;TutorCurso;TutorTelefono;TutorEmail\n";



        // matricula valid format
        // length matricula == 6
        // YY -> started year ; X -> [0-9] ->
        //     II = YYXXXX
        //          i.e ==> 170001 -> 17 -> started year, 0001 -> number
        //     MI = YYmXXX
        //          i.e ==> 17m001 -> 17 -> started year, m -> MI,  001 -> number
        //     ADE = YYiXXX
        //          i.e ==> 17m001 -> 17 -> started year, i -> ADE,  001 -> number

        $anio_start = $anio_imput_int;

        // el anio depende del mes,
        //      de Septiembre a Diciembre es el mismo que el del anio
        //      de Enero a Julio es uno menos que el del anio
        $anio_end = self::getActualYear();

        $control_chart_letter_ARRAY = array("0", "m", "i");
        // suponemos con el "0" que los de ing infor no hay mas de 999 alumnos

        $id_MAX = 100;
        $anio_start_II = $anio_start;
        $nALUMNOSMAX_II = 700;
        $anio_start_MI = 10;
        $nALUMNOSMAX_MI = 100;
        $anio_start_ADE = 17;
        $nALUMNOSMAX_ADE = 100;

        // 16 minutos por año
        $timpoTardarApox = ($anio_end - $anio_start_II) * 16;

        $matriculasACompobar_str = sprintf("%02d", $anio_start_II) . "0000 -> " . sprintf("%02d", $anio_end) . "0" . sprintf("%03d", $nALUMNOSMAX_II) . "\n";
        // Solo para los años qeu estan dispibles
        $matriculasACompobar_str .= sprintf("%02d", $anio_start_MI) . "m000 -> " . sprintf("%02d", $anio_end) . "m" . sprintf("%03d", $nALUMNOSMAX_MI) . "\n";
        $matriculasACompobar_str .= sprintf("%02d", $anio_start_ADE) . "i000 -> " . sprintf("%02d", $anio_end) . "i" . sprintf("%03d", $nALUMNOSMAX_ADE) . "\n";

        $msgTiempoTardar = "Vamos a procesar esats matrículas: \n";
        $msgTiempoTardar .= $matriculasACompobar_str;
        $msgTiempoTardar .= "Tardará aproximadamente: $timpoTardarApox minutos \n";

        $this->getRequest()->hideKeyboard();
        $this->getRequest()->markdown()->sendMessage($msgTiempoTardar);


        for ($anio_current = $anio_start; $anio_current <= $anio_end; $anio_current++) {

            # while control_chart con las letras de control

            $anio_str = sprintf("%02d", $anio_current);

            foreach ($control_chart_letter_ARRAY as &$control_chart_letter) {


                // saltamos que genere mariculas para el grado de MI en los anios que no estaba creado
                if ($anio_current < $anio_start_MI && $control_chart_letter == "m") {
                    break;
                }

                // saltamos que genere mariculas para el grado de ADE en los anios que no estaba creado
                if ($anio_current < $anio_start_ADE && $control_chart_letter == "i") {
                    break;
                }


                if ($control_chart_letter == "0") {
                    $id_MAX = $nALUMNOSMAX_II;
                }
                if ($control_chart_letter == "m") {
                    $id_MAX = $nALUMNOSMAX_MI;
                }
                if ($control_chart_letter == "i") {
                    $id_MAX = $nALUMNOSMAX_ADE;
                }


                for ($id=0; $id<=$id_MAX; $id++) {

                    $id_str = sprintf("%03d", $id);

                    $nMatToSend = $anio_str . $control_chart_letter . $id_str;

                    $mensaje .= $nMatToSend . ";";


                    $tutoria = self::getTutoriaPorMat_CSV($nMatToSend);
                    $mensaje .= $tutoria;
                    sleep(1); // prevenir el baneo


                    $mensaje .= "\n";

                }


            }


        }

        $numeroDeMatriculasConsultadas = substr_count($mensaje, "\n");

        $fichero = 'gente.csv';
        file_put_contents($fichero, $mensaje, LOCK_EX);


//        $this->getRequest()->markdown()->sendMessage($mensaje);
//        $this->getRequest()->sendMessage($mensaje);
        $this->getRequest()->sendDocument($fichero);

        return $this->stopConversation();

    }


    public static function getTutoriaPorMat_CSV($nMatToSend)
    {
        $lineSTR = "";

        $tutoria = TutorRepository::getTutoria(urlencode($nMatToSend));

        if ($tutoria !== null) {

            $alumno = $tutoria[0];
            $tutor = $tutoria[1];


            // AlumnoNombre;AlumnoApellidos;AlumnoCursoEmpieze;
            if ($alumno !== null && $alumno !== [] ) {

                if ($alumno->nombre !== null && $alumno->nombre !== "") {
                    $lineSTR .= "$alumno->nombre";
                }
                $lineSTR .= ";";

                if ($alumno->apellidos !== null && $alumno->apellidos !== "") {
                    $lineSTR .= "$alumno->apellidos";
                }
                $lineSTR .= ";";

                if ($alumno->cursoEmpieze !== null && $alumno->cursoEmpieze !== "") {
                    $lineSTR .= "$alumno->cursoEmpieze";
                }
                $lineSTR .= ";";

            } else {
                $lineSTR .= ";;;";
            }


            // TutorNombre;TutorApellidos;TutorEnlace;TutorDepartamento;TutorDespacho;TutorCurso;TutorTelefono;TutorEmail
            if ($tutor !== null && $tutor !== [] ) {

                if ($tutor->nombre !== null && $tutor->nombre !== "") {
                    $lineSTR .= "$tutor->nombre";
                }
                $lineSTR .= ";";

                if ($tutor->apellidos !== null && $tutor->apellidos !== "") {
                    $lineSTR .= "$tutor->apellidos";
                }
                $lineSTR .= ";";

                if ($tutor->enlace !== null && $tutor->enlace !== "") {
                    $lineSTR .= "$tutor->enlace";
                }
                $lineSTR .= ";";

                if ($tutor->departamento !== null && $tutor->departamento !== "") {
                    $lineSTR .= "$tutor->departamento";
                }
                $lineSTR .= ";";

                if ($tutor->despacho !== null && $tutor->despacho !== "") {
                    $lineSTR .= "$tutor->despacho";
                }
                $lineSTR .= ";";

                if ($tutor->curso !== null && $tutor->curso !== "") {
                    $lineSTR .= "$tutor->curso";
                }
                $lineSTR .= ";";

                if ($tutor->telefono !== null && $tutor->telefono !== "") {
                    $lineSTR .= "$tutor->telefono";
                }
                $lineSTR .= ";";

                if ($tutor->nombreEmail !== null && $tutor->nombreEmail !== "" &&
                    $tutor->dominioEmail !== null && $tutor->dominioEmail !== "") {
                    $lineSTR .= "$tutor->nombreEmail@$tutor->dominioEmail";
                }

            } else {
                $lineSTR .= ";;;;;;;";
            }


        }

        return $lineSTR;

    }

    private function getActualYear()
    {
        $year = intval(date("y"));

        if (intval(date("m")) <= 7)
            $year--;

        return $year;
    }


}