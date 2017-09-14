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
    const ATRAS = 'Atras';

    const NOTES_ANIO_START = 'anio_start';
    const NOTES_ANIO_END = 'anio_end';

    const ANIO_ETSIINF_START = 1977;

    const ID_NMAT_MAX = 100;
    const N_ALUM_MAX_II = 700;
    const ANIO_START_MI = 10;
    const N_ALUM_MAX_MI = 100;
    const ANIO_START_ADE = 16;
    const N_ALUM_MAX_ADE = 100;


    public function processGetTextForSearch($text)
    {
        $this->getConversation();

        $this->getRequest()->sendAction(Request::ACTION_TYPING);

        $keyboard [] = [self::CANCELAR];

        if ($this->isProcessed() || empty($text))
        {
            return $this->getRequest()->markdown()->keyboard($keyboard)
                ->sendMessage("Introduce el *año de comienzo de la búsqueda* (2009, 1997, 2011 etc...) por el cual quieres comenzar la búsqueda.");
        }
        if ($text === self::CANCELAR)
        {
            return $this->cancelConversation();
        }

        $this->getConversation()->notes[self::NOTES_ANIO_START] = $text;
        return $this->nextStep();
    }


    public function processAnioEnd($text)
    {

        if ($text === self::CANCELAR) {
            return $this->cancelConversation();
        }

        $this->getRequest()->sendAction(Request::ACTION_TYPING);

        $anio_start_imput_str = $this->getConversation()->notes[self::NOTES_ANIO_START];
        if (strlen($anio_start_imput_str) !== 4
            || intval($anio_start_imput_str) == 0
            || is_int(intval($anio_start_imput_str)) == false
            || intval($anio_start_imput_str) < self::ANIO_ETSIINF_START
            || intval($anio_start_imput_str) > intval(date('Y')) )
        {
            return $this->previousStep();
        }

        $keyboard [] = [self::CANCELAR, self::ATRAS];

        if ($this->isProcessed() || empty($text))
        {
            return $this->getRequest()->markdown()->keyboard($keyboard)
                ->sendMessage("Introduce el *año de fin de la búsqueda* (2009, 1997, 2011 etc... Puedes poner el mismo del cominezo y solo se buscaría en ese año).");
        }
        if ($text === self::ATRAS)
        {
            return $this->previousStep();
        }
        if ($text === self::CANCELAR)
        {
            return $this->cancelConversation();
        }

        $this->getConversation()->notes[self::NOTES_ANIO_END] = $text;
        return $this->nextStep();

    }

    public function processCheck($text)
    {

        if ($text === self::ATRAS)
        {
            return $this->previousStep();
        }
        if ($text === self::CANCELAR)
        {
            return $this->cancelConversation();
        }

        $this->getRequest()->sendAction(Request::ACTION_TYPING);

        $anio_end_imput_str = $this->getConversation()->notes[self::NOTES_ANIO_END];
        if (strlen($anio_end_imput_str) !== 4
            || intval($anio_end_imput_str) == 0
            || is_int(intval($anio_end_imput_str)) == false
            || intval($anio_end_imput_str) < self::ANIO_ETSIINF_START
            || intval($anio_end_imput_str) > intval(date('Y')) )
        {
            return $this->previousStep();
        }

        $anio_start_imput_str = $this->getConversation()->notes[self::NOTES_ANIO_START];
        if (intval($anio_start_imput_str) > intval($anio_end_imput_str))
        {
            return $this->previousStep();
        }



        $this->getConversation()->notes[self::NOTES_ANIO_END] = $text;
        return $this->nextStep();

    }



    public function processGetAllTutories($text)
    {

        // header
        $mensaje = "AlumnoNumeroMatricula;AlumnoNombre;AlumnoApellidos;AlumnoCursoEmpieze;TutorNombre;TutorApellidos;TutorEnlace;TutorDepartamento;TutorDespacho;TutorCurso;TutorTelefono;TutorEmail\n";


        $anio_start_imput_str = $this->getConversation()->notes[self::NOTES_ANIO_START];
        // pasamos de 2009 a 09, y luego a valor int -> 9
        $anio_start_imput_str_cut = substr($anio_start_imput_str, -2);
        $anio_start_imput_int = intval($anio_start_imput_str_cut);


        $anio_end_imput_str = $this->getConversation()->notes[self::NOTES_ANIO_END];
        // pasamos de 2009 a 09, y luego a valor int -> 9
        $anio_end_imput_str_cut = substr($anio_end_imput_str, -2);
        $anio_end_imput_int = intval($anio_end_imput_str_cut );





        // matricula valid format
        // length matricula == 6
        // YY -> started year ; X -> [0-9] ->
        //     II = YYXXXX
        //          i.e ==> 170001 -> 17 -> started year, 0001 -> number
        //     MI = YYmXXX
        //          i.e ==> 17m001 -> 17 -> started year, m -> MI,  001 -> number
        //     ADE = YYiXXX
        //          i.e ==> 17m001 -> 17 -> started year, i -> ADE,  001 -> number

        $anio_start = $anio_start_imput_int;

        // el anio depende del mes,
        //      de Septiembre a Diciembre es el mismo que el del anio
        //      de Enero a Julio es uno menos que el del anio
        $anio_end = $anio_end_imput_int;
        // $anio_end = self::getActualYear();

        $control_chart_letter_ARRAY = array("0", "m", "i");
        // suponemos con el "0" que los de ing infor no hay mas de 999 alumnos

        $id_MAX = self::ID_NMAT_MAX;
        $anio_start_II = $anio_start;
        $nALUMNOSMAX_II = self::N_ALUM_MAX_II;
        $anio_start_MI = self::ANIO_START_MI;
        $nALUMNOSMAX_MI = self::N_ALUM_MAX_MI;
        $anio_start_ADE = self::ANIO_START_ADE;
        $nALUMNOSMAX_ADE = self::N_ALUM_MAX_ADE;

        // 16 minutos por año aprox
        $timpoTardarApox = (($anio_end + 1)  - $anio_start_II) * 16;

        $matriculasACompobar_str = "";
        if ($anio_start_II <= $anio_end)
            $matriculasACompobar_str .= " - " . sprintf("%02d", $anio_start_II) . "0000 -> " . sprintf("%02d", $anio_end) . "0" . sprintf("%03d", $nALUMNOSMAX_II) . "\n";
        // Solo para los años que estan dispibles
        if ($anio_start_MI <= $anio_end)
            $matriculasACompobar_str .= " - " . sprintf("%02d", $anio_start_MI) . "m000 -> " . sprintf("%02d", $anio_end) . "m" . sprintf("%03d", $nALUMNOSMAX_MI) . "\n";
        if ($anio_start_ADE <= $anio_end)
            $matriculasACompobar_str .= " - " . sprintf("%02d", $anio_start_ADE) . "i000 -> " . sprintf("%02d", $anio_end) . "i" . sprintf("%03d", $nALUMNOSMAX_ADE) . "\n";

        $msgTiempoTardar = "Vamos a procesar estas matrículas: \n";
        $msgTiempoTardar .= $matriculasACompobar_str;
        $msgTiempoTardar .= "*Tardará aproximadamente: $timpoTardarApox minutos.*\n";
        $msgTiempoTardar .= "_Su conversación se bloquerá en ese tiempo_\n";

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
                    // echo $tutoria . PHP_EOL;
                    $mensaje .= $tutoria;

                    sleep(1); // prevenir el baneo


                    $mensaje .= "\n";

                }


            }


        }

        $numeroDeMatriculasConsultadas = substr_count($mensaje, "\n");

        $fichero = 'gente.csv';
        file_put_contents($fichero, $mensaje, LOCK_EX);


        // $this->getRequest()->markdown()->sendMessage($mensaje);
        // $this->getRequest()->sendMessage($mensaje);
        // $this->getRequest()->sendDocument($fichero);

        // unlink($fichero);

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