<?php
/**
 * Created by PhpStorm.
 * User: Sergio
 */

namespace app\commands\user;
use app\commands\base\Request;
use app\commands\user\AsignaturasCommand;
use app\models\repositories\SubjectRepository;
use app\models\repositories\CalendarRepository;
use app\commands\base\BaseUserCommand;

/**
 * User "/asignaturasBuscador" command
 */
class AsignaturasBuscadorCommand extends BaseUserCommand
{
    /**
     * {@inheritdoc}
     */
    public $enabled = true;
    protected $name = 'asignaturasBuscador';
    protected $description = 'Consulta información sobre las asignaturas, sus profesores y las tutorias, buscando su nombre.';
    protected $usage = '/asignaturasBuscador';
    protected $version = '0.1.0';
    protected $need_mysql = true;

    const PROFESORES = 'Profesores y Tutorías';
    const HORARIO = 'Horario';
    const CRITERIOS = 'Criterios de evaluación';
    const ACTIVIDADES = 'Actividades de evaluación';
    const RECURSOS = 'Recursos didácticos';
    const GUIA_DOCENTE = 'Guía docente';


    const CANCELAR = 'Cancelar';
    const ATRAS = 'Atrás';
    const NUEVA_BUSQUEDA = 'Nueva Búsqueda';

    public $subjectlist = [];


    /**
     * @param $text
     * @return \Longman\TelegramBot\Entities\ServerResponse
     */
    public function processGetTextForSearch($text)
    {
        $this->getConversation();

        $this->getRequest()->sendAction(Request::ACTION_TYPING);
        $keyboard [] = [self::CANCELAR];

        if ($this->isProcessed() || empty($text))
        {
            $msg = "Introduce el nombre de la asignatura que deseas buscar información (con acentos si los tiene):";
            $msg .= "Tenga paciencia, la búsqueda tardará un poco...";
            return $this->getRequest()->markdown()->keyboard($keyboard)->sendMessage($msg);
        }
        if ($text === self::CANCELAR)
        {
            return $this->cancelConversation();
        }

        $this->getConversation()->notes['text'] = $text;
        return $this->nextStep();
    }

    /**
     * @param $text
     * @return \Longman\TelegramBot\Entities\ServerResponse
     */
    public function processReturnSearch($text)
    {

        if ($text === self::CANCELAR)
        {
            return $this->cancelConversation();
        }
        else if ($text === self::NUEVA_BUSQUEDA)
        {
            return $this->previousStep();
        }

        $textForSearch = $this->getConversation()->notes['text'];

        $this->getRequest()->sendAction(Request::ACTION_TYPING);

        $year = SubjectRepository::getActualYear();
        $year2 = 2000 + SubjectRepository::getYear2($year);

        $asignaturasPosibles = SubjectRepository::getSubjectsCodeByTextMatched($textForSearch);

        if (empty($asignaturasPosibles))
        {
            $this->getRequest()->sendMessage("No hay ninguna asignatura con el nombre: \"$textForSearch\"");
            return $this->previousStep();
        }

        $opts4 = [];
        foreach ($asignaturasPosibles as $asignatura) {
            $codigo = $asignatura->codigo;
            $nombre = $asignatura->nombre;
            $curso = intval($asignatura->curso);

            foreach ($asignatura->imparticion as $imparticion)
            {
                $semestre = $imparticion->codigo_duracion;

                $semestre_curso = $curso * 2;
                if ($semestre == "1S") {
                    $semestre_curso = $semestre_curso - 1;
                }
                $semestre_curso = $semestre_curso . "S";

                $guia_pdf = $imparticion->guia_pdf;
                $plan = "NOGUIA";
                if (!empty($guia_pdf)) {
                    $plan = explode("_", substr($guia_pdf, 61));
                    $plan = $plan[0];
                }
                array_push($opts4, "[$plan][$semestre_curso] $nombre");
                $opts4SubjectsCode[$asignatura->codigo] = "[$plan] [$semestre_curso] $nombre";

                //echo "[$plan] [$year-$year2] [$curso] [$semestre] [$semestre_curso] $nombre($codigo) [$guia_pdf]\n";
                //echo "[$plan] [$year-$year2] [$semestre_curso] $nombre($codigo) [$guia_pdf]\n";
            }
        }

        if (count($opts4) > 20)
        {
            $this->getRequest()->sendMessage("Hay demasiadas asignaturas que coinciden con esa búsqueda \"$textForSearch\". Por favor introduce alguna palabra mas para refinar la siguiente búsqueda.");
            return $this->previousStep();
        }

        // If only have 1 matched
        if (count($opts4) == 1)
        {
            $keyboardSubjectSelectedName = array_values($opts4)[0];
        }
        else
        {
            $cancel = [self::CANCELAR,self::ATRAS];
            $keyboard = array_chunk($opts4, 1);
            $keyboard [] = $cancel;

            $this->getRequest()->keyboard($keyboard);

            if ($this->isProcessed() || empty($text))
            {
                $msg = "Selecciona la asignatura de la cual necesitas información (todas son del curso $year-$year2)"."\n";
                $msg .= "Si la asginatura no está, puede ser que no este colgada la guía."."\n";
                $msg .= "Recuerda que la búsqueda es sensible a acentos."."\n";
                $msg .= "El formato de las opciones del teclado es el siguiente:"."\n";
                $msg .= "`[plan] [semestre_curso] nombreAsignatura`"."\n";
                return $this->getRequest()->markdown()->sendMessage($msg);

            }

            if (!(in_array($text, $opts4) || in_array($text, $cancel)))
            {
                return $this->getRequest()->sendMessage('Selecciona una opción del teclado por favor:');
            }


            if ($text === self::CANCELAR)
            {
                return $this->cancelConversation();
            }
            if ($text === self::ATRAS)
            {
                return $this->previousStep();
            }


            $keyboardSubjectSelectedName = $text;
        }


        $subjectIndexSelectedKeyboard = array_search($keyboardSubjectSelectedName,$opts4);
        $subjectIndexSelected = $subjectIndexSelectedKeyboard - 1;
        $subjectSelected = $keyboardSubjectSelectedName = array_values($asignaturasPosibles)[$subjectIndexSelected];
        $subjectSelectedAPIPointJSON = $subjectSelected->imparticion[0]->guia_json;
        $subjectSelectedDegree = $subjectSelected->curso;
        $subjectSelectedGroupsArray = $subjectSelected->imparticion[0]->grupos_matricula;

        $this->getConversation()->notes['subjectAPIPoint'] = $subjectSelectedAPIPointJSON;
        $this->getConversation()->notes['degree'] = $subjectSelectedDegree;
        $this->getConversation()->notes['groupsArray'] = $subjectSelectedGroupsArray;

        return $this->nextStep();
    }

    /**
     * @param $text
     * @return \Longman\TelegramBot\Entities\ServerResponse
     * @throws \Exception
     */
    public function processShowInfoSubject($text)
    {
        $this->getRequest()->sendAction(Request::ACTION_TYPING);

        $subjectSelectedAPIPointJSON = $this->getConversation()->notes['subjectAPIPoint'];
        $subjectSelectedDegree = $this->getConversation()->notes['degree'];

        try
        {
            $subject = SubjectRepository::getSubjectByAPIPoint($subjectSelectedAPIPointJSON);
        }
        catch (\Exception $exception)
        {
            if (preg_match('/Unable to connect to /',$exception->getMessage()))
            {
                $msge = "Parece que la API de la UPM esta caida.\n";
                print("Unable to connect\n");
                print($exception->getMessage());
                print($exception->getTraceAsString());
            }
            elseif ($exception->getMessage() == "Unable to parse response as JSON")
            {
                $msge = "Parece que la asignatura escogida *no tiene información disponible en estos momentos*.\n" .
                    "Esto puede suceder al escoger una asignatura del semestre siguiente al actual (la cual no estan las guias " .
                    "aun redactadas), o bien al intentar acceder a una asignatura de créditos optativos, la cual no tiene guía docente.\n" .
                    "*Por favor, selecciona otra asignatura de la lista.*";
                print("No se ha interpretado el JSON de la petición.\n");
                print($exception->getMessage());
                print($exception->getTraceAsString());
            }
            elseif ($exception->getMessage() == "Attempting to send a request before defining a URI endpoint.")
            {
                $msge = "Parece que la asignatura escogida *no tiene información disponible en estos momentos*.\n" .
                    "Esto puede suceder al escoger una asignatura del semestre siguiente al actual (la cual no estan las guias " .
                    "aun redactadas), o bien al intentar acceder a una asignatura de créditos optativos, la cual no tiene guía docente.\n" .
                    "*Por favor, selecciona otra asignatura de la lista.*\n";
                print("Attempting to send a request before defining a URI endpoint.");
                print($exception->getMessage());
                print($exception->getTraceAsString());
            }
            else
            {
                $msge = "Ocurrió un error inesperado.";
                print($msge);
                throw $exception;
            }

            $msge .= "\nVuelva a intentarlo mas tarde.";
            $result = $this->getRequest()->markdown()->sendMessage($msge."\n\n");
            return $result;
        }

        $numProfesores = count($subject->profesores);


        $message = "Información sobre...\n*$subject->nombre*\nPlan: *$subject->plan*\nAño: *$subject->anio*\n" .
            "Curso: *$subjectSelectedDegree*\nSemestre: *$subject->semestre*\nDepartamento: *$subject->depto*\nTipo: *$subject->caracter*\n" .
            "Créditos: *$subject->ects ECTS*\nProfesores: *$numProfesores profesores*\n\n" .
            "Selecciona mediante el teclado una opción.\n";

/*        $message = "Información sobre...\n*$subject->nombre*\nPlan: *$subject->plan*\nAño: *$subject->anio*\n" .
            "Departamento: *$subject->depto*\nTipo: *$subject->caracter*\n" .
            "Créditos: *$subject->ects ECTS*\nProfesores: *$numProfesores profesores*\n\n" .
            "Selecciona mediante el teclado una opción.\n";*/


        $cancel = [self::CANCELAR, self::ATRAS];
        $keyboard = [[self::GUIA_DOCENTE], [self::PROFESORES], $cancel];
//        $keyboard = [[self::PROFESORES], [self::HORARIO], [self::CRITERIOS], [self::ACTIVIDADES], [self::RECURSOS], [self::GUIA_DOCENTE], $cancel];
        $this->getRequest()->keyboard($keyboard);
        if($this->isProcessed() || empty($text))
        {
            return $this->getRequest()->markdown()->sendMessage($message);
        }
        if($text == self::PROFESORES) {
            return $this->nextStep('teacher');
        }
        if($text == self::HORARIO)
        {
            return $this->nextStep('timetable');
        }
        if($text == self::CRITERIOS) {
//            return self::processCriteria();
            return $this->nextStep('criteria');
        }
        if($text == self::ACTIVIDADES) {
//            return self::processActivities();
            return $this->nextStep('activities');
        }
        if($text == self::RECURSOS) {
//            return self::processResources();
            return $this->nextStep('resources');
        }
        if($text == self::GUIA_DOCENTE) {
            return $this->nextStep('sendGuide');
        }


        if ($text === self::CANCELAR)
        {
            return $this->cancelConversation();
        }
        if ($text === self::ATRAS)
        {
            return $this->previousStep();
        }

        return $this->getRequest()->sendMessage('Selecciona una opción del teclado por favor:');
    }

    /**
     * @return \Longman\TelegramBot\Entities\ServerResponse
     * @throws \Exception
     */
    public function processSendGuide()
    {
        $this->getRequest()->sendAction(Request::ACTION_TYPING);

        $subjectSelectedAPIPointJSON = $this->getConversation()->notes['subjectAPIPoint'];
        $subjectSelectedDegree = $this->getConversation()->notes['degree'];

        // TODO: Mirar el error SSL.
        //$guiaPDF=SubjectRepository::getGuia($subject->guia);
        //$cap = "Aquí te enviamos la guia docente de $subject->nombre";
        //$this->getRequest()->caption("$cap")->sendDocument($guiaPDF);
        try
        {
            $subject = SubjectRepository::getSubjectByAPIPoint($subjectSelectedAPIPointJSON);
        }
        catch (\Exception $exception)
        {
            if (preg_match('/Unable to connect to /',$exception->getMessage()))
            {
                $msge = "Parece que la API de la UPM esta caida.\n";
                print("Unable to connect\n");
                print($exception->getMessage());
                print($exception->getTraceAsString());
            }
            elseif ($exception->getMessage() == "Unable to parse response as JSON")
            {
                $msge = "Parece que la asignatura escogida *no tiene información disponible en estos momentos*.\n" .
                    "Esto puede suceder al escoger una asignatura del semestre siguiente al actual (la cual no estan las guias " .
                    "aun redactadas), o bien al intentar acceder a una asignatura de créditos optativos, la cual no tiene guía docente.\n" .
                    "*Por favor, selecciona otra asignatura de la lista.*";
                print("No se ha interpretado el JSON de la petición.\n");
                print($exception->getMessage());
                print($exception->getTraceAsString());
            }
            elseif ($exception->getMessage() == "Attempting to send a request before defining a URI endpoint.")
            {
                $msge = "Parece que la asignatura escogida *no tiene información disponible en estos momentos*.\n" .
                    "Esto puede suceder al escoger una asignatura del semestre siguiente al actual (la cual no estan las guias " .
                    "aun redactadas), o bien al intentar acceder a una asignatura de créditos optativos, la cual no tiene guía docente.\n" .
                    "*Por favor, selecciona otra asignatura de la lista.*\n";
                print("Attempting to send a request before defining a URI endpoint.");
                print($exception->getMessage());
                print($exception->getTraceAsString());
            }
            else
            {
                $msge = "Ocurrió un error inesperado.";
                print($msge);
                throw $exception;
            }

            $msge .= "\nVuelva a intentarlo mas tarde.";
            $result = $this->getRequest()->markdown()->sendMessage($msge."\n\n");
            return $result;
        }

        //print_r($subject);
        $nombre = $subject->nombre;
        $curso = $subjectSelectedDegree;
        $semestre = $subject->semestre;
        $guia_pdf = $subject->guia;
        $plan = explode("_", substr($guia_pdf, 61))[0];
        $year = $subject->anio;
        $msg = "Aquí tienes la guia docente de:\n*[$plan] [$year] [$curso] [$semestre] $nombre*\n$guia_pdf";
        // $msg = "Aquí tienes la guia docente de:\n*[$plan] $nombre*\n$guia_pdf";

        $result = $this->getRequest()->hideKeyboard()->markdown()->sendMessage($msg);
        $this->stopConversation();
        return $result;
    }

    /**
     * TODO: Not finish
     * @return \Longman\TelegramBot\Entities\ServerResponse
     * @throws \Exception
     */
    public function processTimetable()
    {
        $this->getRequest()->sendAction(Request::ACTION_TYPING);

        $subjectSelectedAPIPointJSON = $this->getConversation()->notes['subjectAPIPoint'];
        $subjectSelectedDegree = $this->getConversation()->notes['degree'];

        try
        {
            $subject = SubjectRepository::getSubjectByAPIPoint($subjectSelectedAPIPointJSON);
        }
        catch (\Exception $exception)
        {
            throw $exception;
        }


        //print_r($subject);
        $nombre = $subject->nombre;
        $curso = $subjectSelectedDegree;
        $semestre = $subject->semestre;

        try
        {
            // TODO: send calendar
//        $timetables = CalendarRepository::getTimetables($this->getConversation()->notes['degree'])[$this->getConversation()->notes['period']];
//        $timetable = $timetables[$text];
        }
        catch (\Exception $exception)
        {
            if (preg_match('/Unable to connect to /',$exception->getMessage()))
            {
                $msge = "Parece que la API de la UPM esta caida.\n";
                print("Unable to connect\n");
                print($exception->getMessage());
                print($exception->getTraceAsString());
            }
            elseif ($exception->getMessage() == "Unable to parse response as JSON")
            {
                $msge = "Parece que la asignatura escogida *no tiene información disponible en estos momentos*.\n" .
                    "Esto puede suceder al escoger una asignatura del semestre siguiente al actual (la cual no estan las guias " .
                    "aun redactadas), o bien al intentar acceder a una asignatura de créditos optativos, la cual no tiene guía docente.\n" .
                    "*Por favor, selecciona otra asignatura de la lista.*";
                print("No se ha interpretado el JSON de la petición.\n");
                print($exception->getMessage());
                print($exception->getTraceAsString());
            }
            elseif ($exception->getMessage() == "Attempting to send a request before defining a URI endpoint.")
            {
                $msge = "Parece que la asignatura escogida *no tiene información disponible en estos momentos*.\n" .
                    "Esto puede suceder al escoger una asignatura del semestre siguiente al actual (la cual no estan las guias " .
                    "aun redactadas), o bien al intentar acceder a una asignatura de créditos optativos, la cual no tiene guía docente.\n" .
                    "*Por favor, selecciona otra asignatura de la lista.*\n";
                print("Attempting to send a request before defining a URI endpoint.");
                print($exception->getMessage());
                print($exception->getTraceAsString());
            }
            else
            {
                $msge = "Ocurrió un error inesperado.";
                print($msge);
                throw $exception;
            }

            $msge .= "\nVuelva a intentarlo mas tarde.";
            $result = $this->getRequest()->markdown()->sendMessage($msge."\n\n");
            return $result;
        }

        $year = $subject->anio;
        $msg = "Aquí tienes el horario de:\n*[$plan] [$year-$year2] [$curso] [$semestre] $nombre*\n$horario";

        $result = $this->getRequest()->hideKeyboard()->markdown()->sendMessage($msg);
        $result = $this->getRequest()->hideKeyboard()->caption($timetable->caption)->sendDocument($timetable->link);
        $this->stopConversation();
        return $result;
    }

    public function processTeacher($text)
    {
        $this->getRequest()->sendAction(Request::ACTION_TYPING);

        $subjectSelectedAPIPointJSON = $this->getConversation()->notes['subjectAPIPoint'];

        try
        {
            $subject = SubjectRepository::getSubjectByAPIPoint($subjectSelectedAPIPointJSON);
        }
        catch (\Exception $exception)
        {
            if (preg_match('/Unable to connect to /',$exception->getMessage()))
            {
                $msge = "Parece que la API de la UPM esta caida.\n";
                print("Unable to connect\n");
                print($exception->getMessage());
                print($exception->getTraceAsString());
            }
            elseif ($exception->getMessage() == "Unable to parse response as JSON")
            {
                $msge = "Parece que la asignatura escogida *no tiene información disponible en estos momentos*.\n" .
                    "Esto puede suceder al escoger una asignatura del semestre siguiente al actual (la cual no estan las guias " .
                    "aun redactadas), o bien al intentar acceder a una asignatura de créditos optativos, la cual no tiene guía docente.\n" .
                    "*Por favor, selecciona otra asignatura de la lista.*";
                print("No se ha interpretado el JSON de la petición.\n");
                print($exception->getMessage());
                print($exception->getTraceAsString());
            }
            elseif ($exception->getMessage() == "Attempting to send a request before defining a URI endpoint.")
            {
                $msge = "Parece que la asignatura escogida *no tiene información disponible en estos momentos*.\n" .
                    "Esto puede suceder al escoger una asignatura del semestre siguiente al actual (la cual no estan las guias " .
                    "aun redactadas), o bien al intentar acceder a una asignatura de créditos optativos, la cual no tiene guía docente.\n" .
                    "*Por favor, selecciona otra asignatura de la lista.*\n";
                print("Attempting to send a request before defining a URI endpoint.");
                print($exception->getMessage());
                print($exception->getTraceAsString());
            }
            else
            {
                $msge = "Ocurrió un error inesperado.";
                print($msge);
                throw $exception;
            }

            $msge .= "\nVuelva a intentarlo mas tarde.";
            $result = $this->getRequest()->markdown()->sendMessage($msge."\n\n");
            return $result;
        }

        $profesoresKB = [];

        if (count($subject->profesores) !== 0)
        {
            $mensaje = "Los siguientes profesores pueden ayudarte con *$subject->nombre*:\n\n";

            foreach ($subject->profesores as $profesor)
            {
                if ($profesor->coordinador == true)
                {
                    $mensaje .= "- *$profesor->nombre $profesor->apellidos* (coordinador) ($profesor->despacho)\n";
                }
                else
                {
                    $mensaje .= "- *$profesor->nombre $profesor->apellidos ($profesor->despacho)*\n";
                }

                $profesoresKB[] = "$profesor->nombre $profesor->apellidos";

            }

            $mensaje .= "\n¿De qué profesor deseas obtener más información?";
        }
        else
        {
            $mensaje = "No hay ningún profesor asignado.";
        }

        $cancel = [self::CANCELAR, self::ATRAS];
        $keyboard = array_chunk($profesoresKB, 2);
        $keyboard [] = $cancel;

        $this->getRequest()->keyboard($keyboard);

        if ($this->isProcessed() || empty($text))
        {
            return $this->getRequest()->markdown()->sendMessage($mensaje);
        }

        if (!(in_array($text, $profesoresKB) || in_array($text, $cancel)))
        {
            return $this->getRequest()->sendMessage('Selecciona una opción del teclado por favor:');
        }

        if ($text === self::CANCELAR)
        {
            return $this->cancelConversation();
        }
        if ($text === self::ATRAS)
        {
            return $this->previousStep();
        }

        $this->getConversation()->notes['teacher'] = $text;
        return $this->nextStep();

    }

    public function processTeacherInfo($text)
    {

        $this->getRequest()->sendAction(Request::ACTION_TYPING);

        $cancel = [self::CANCELAR, self::ATRAS];
        $keyboard [] = $cancel;

        $mailIcon = "\xF0\x9F\x93\xA7";
        $departmentIcon = "\xF0\x9F\x91\x94";
        $clockIcon = "\xF0\x9F\x95\x92";
        $alertIcon = "\xE2\x9A\xA0";

        $selectedTeacher = $this->getConversation()->notes['teacher'];

        $subjectSelectedAPIPointJSON = $this->getConversation()->notes['subjectAPIPoint'];

        try
        {
            $subject = SubjectRepository::getSubjectByAPIPoint($subjectSelectedAPIPointJSON);
        }
        catch (\Exception $exception)
        {
            if (preg_match('/Unable to connect to /',$exception->getMessage()))
            {
                $msge = "Parece que la API de la UPM esta caida.\n";
                print("Unable to connect\n");
                print($exception->getMessage());
                print($exception->getTraceAsString());
            }
            elseif ($exception->getMessage() == "Unable to parse response as JSON")
            {
                $msge = "Parece que la asignatura escogida *no tiene información disponible en estos momentos*.\n" .
                    "Esto puede suceder al escoger una asignatura del semestre siguiente al actual (la cual no estan las guias " .
                    "aun redactadas), o bien al intentar acceder a una asignatura de créditos optativos, la cual no tiene guía docente.\n" .
                    "*Por favor, selecciona otra asignatura de la lista.*";
                print("No se ha interpretado el JSON de la petición.\n");
                print($exception->getMessage());
                print($exception->getTraceAsString());
            }
            elseif ($exception->getMessage() == "Attempting to send a request before defining a URI endpoint.")
            {
                $msge = "Parece que la asignatura escogida *no tiene información disponible en estos momentos*.\n" .
                    "Esto puede suceder al escoger una asignatura del semestre siguiente al actual (la cual no estan las guias " .
                    "aun redactadas), o bien al intentar acceder a una asignatura de créditos optativos, la cual no tiene guía docente.\n" .
                    "*Por favor, selecciona otra asignatura de la lista.*\n";
                print("Attempting to send a request before defining a URI endpoint.");
                print($exception->getMessage());
                print($exception->getTraceAsString());
            }
            else
            {
                $msge = "Ocurrió un error inesperado.";
                print($msge);
                throw $exception;
            }

            $msge .= "\nVuelva a intentarlo mas tarde.";
            $result = $this->getRequest()->markdown()->sendMessage($msge."\n\n");
            return $result;
        }


        if ($text === self::CANCELAR)
        {
            return $this->cancelConversation();
        }
        if ($text === self::ATRAS)
        {
            return $this->previousStep();
        }

        $mensaje = "";
        if ($this->isProcessed() || empty($text))
        {
            foreach ($subject->profesores as $profesor)
            {
                if (("$profesor->nombre $profesor->apellidos") == $selectedTeacher)
                {
                    $mensaje = "Información sobre...\n*$profesor->nombre $profesor->apellidos*\n".
                        "$mailIcon Email: $profesor->email\n"."$departmentIcon Despacho: *$profesor->despacho*\n";


                    if (count($profesor->tutorias) !== 0)
                    {
                        $mensaje .= "\n$clockIcon Horarios de tutorias:\n";
                        foreach ($profesor->tutorias as $tutoria)
                        {
                            $mensaje .= $tutoria->getTutoriaMessage() . "\n";
                        }

                    }
                    else
                    {
                        $mensaje .="\n$alertIcon *El profesor no ha especificado un horario de tutorias válido.*\n".
                            "Si tienes alguna duda ponte en contacto vía email.\n";
                    }

                    $mensaje .= "\n*Si deseas obtener información de otro profesor, pulsa Atrás.\nEn caso contrario, pulsa Cancelar.*";
                }
            }
            return $this->getRequest()->markdown()->keyboard($keyboard)->sendMessage($mensaje);
        }

        if (!in_array($text, $cancel))
        {
            return $this->getRequest()->sendMessage('Selecciona una opción del teclado por favor:');
        }

    }

    /**
     * @param $text
     * @return \Longman\TelegramBot\Entities\ServerResponse
     */
    public function processCriteria($text)
    {
        $this->getRequest()->sendAction(Request::ACTION_TYPING);
        $this->getRequest()->sendMessage('NOT IMPLEMENTED');
        return $this->previousStep();
    }

    /**
     * @param $text
     * @return \Longman\TelegramBot\Entities\ServerResponse
     */
    public function processActivities($text)
    {
        $this->getRequest()->sendAction(Request::ACTION_TYPING);
        $this->getRequest()->sendMessage('NOT IMPLEMENTED');
        return $this->previousStep();
    }

    /**
     * @param $text
     * @return \Longman\TelegramBot\Entities\ServerResponse
     */
    public function processResources($text)
    {
        $this->getRequest()->sendAction(Request::ACTION_TYPING);
        $this->getRequest()->sendMessage('NOT IMPLEMENTED');
        return $this->previousStep();
    }


}