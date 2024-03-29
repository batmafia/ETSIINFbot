<?php
/**
 * Created by PhpStorm.
 * User: Diego
 * Date: 30/9/16
 * Time: 13:01
 */

namespace app\commands\user;
use app\commands\base\Request;
use app\models\repositories\SubjectRepository;
use app\commands\base\BaseUserCommand;

/**
 * User "/asignaturas" command
 */
class AsignaturasCommand extends BaseUserCommand
{
    /**
     * {@inheritdoc}
     */
    public $enabled = true;
    protected $name = 'asignaturas';
    protected $description = 'Consulta información sobre las asignaturas, sus profesores y las tutorias.';
    protected $usage = '/asignaturas';
    protected $version = '0.2.0';
    protected $need_mysql = true;

    const PROFESORES = 'Profesores y Tutorías';
    const HORARIO = 'Horario';
    const CRITERIOS = 'Criterios de evaluación';
    const ACTIVIDADES = 'Actividades de evaluación';
    const RECURSOS = 'Recursos didácticos';
    const GUIA_DOCENTE = 'Guía docente';

    const CANCELAR = 'Cancelar';
    const ATRAS = 'Atrás';

    public $subjectlist = [];


    private function getActualYear()
    {
        $year = intval(date("Y"));

        if (intval(date("m")) <= 7)
            $year--;

        return $year;
    }

    public function processGetPlan($text)
    {
        $this->getRequest()->sendAction(Request::ACTION_TYPING);

        $this->getConversation();

        // ETSIINF = 10; PSC = Primer y Segundo Ciclo; GRA = Grado
        try
        {
            $plans = SubjectRepository::getPlansFromCenter('10','PSC','GRA,MOF',$this->getActualYear());
        }
        catch (\Exception $exception)
        {
            if (preg_match('/Unable to connect to /',$exception->getMessage()))
            {
                $msge = "Parece que la API de la UPM esta caida.";
            }
            elseif ($exception->getMessage() == "Unable to parse response as JSON")
            {
                $msge = "Parece que la API de la UPM esta caida.";
                print("No se ha interpretado el JSON de la petición.");
                print($exception->getMessage());
                print($exception->getTraceAsString());
            }
            else
            {
                $msge = "Ocurrió un error inesperado.";
                print($msge);
                throw $exception;
            }
            $msge .= " Vuelva a intentarlo mas tarde.";
            $result = $this->getRequest()->markdown()->sendMessage($msge."\n\n");
            return $result;
        }

        foreach ($plans as $plan){
            $options[$plan->codigo] =  "$plan->nombre";
        }

        natcasesort($options);

        $cancel = [self::CANCELAR];
        $keyboard = array_chunk(($options), 1);
        $keyboard [] = [self::CANCELAR];


        $this->getRequest()->keyboard($keyboard);
        if ($this->isProcessed() || empty($text))
        {
            return $this->getRequest()->markdown()->sendMessage("_Actualmente algunos datos no están disponibles por errores en la API de la UPM_.\n\nSelecciona tu plan de estudios:");
        }
        if (!(in_array($text, $options) || in_array($text, $cancel)))
        {
            return $this->getRequest()->sendMessage('Selecciona una opción del teclado por favor:');
        }
        if (in_array($text, $cancel))
        {
            return $this->cancelConversation();
        }
        $this->getConversation()->notes['plan'] = array_search($text,$options);
        return $this->nextStep();
    }

    public function processShowCourse($text)
    {

        $this->getRequest()->sendAction(Request::ACTION_TYPING);

        $selectedPlan = $this->getConversation()->notes['plan'];

        try
        {
            $ordenadas = SubjectRepository::getSubjectsList($selectedPlan, $this->getActualYear());
        }
        catch (\Exception $exception)
        {
            if (preg_match('/Unable to connect to /',$exception->getMessage()))
            {
                $msge = "Parece que la API de la UPM esta caida.";
            }
            elseif ($exception->getMessage() == "Unable to parse response as JSON")
            {
                $msge = "Parece que la API de la UPM esta caida.";
                print("No se ha interpretado el JSON de la petición.");
                print($exception->getMessage());
                print($exception->getTraceAsString());
            }
            else
            {
                $msge = "Ocurrió un error inesperado.";
                print($msge);
                throw $exception;
            }
            $msge .= " Vuelva a intentarlo mas tarde.";
            $result = $this->getRequest()->markdown()->sendMessage($msge."\n\n");
            return $result;
        }

        $opts2 = array_keys($ordenadas);
        if(count($opts2) === 1)
        {
            if($text === self::ATRAS)
            {
                return $this->previousStep();
            }
            else
            {
                $this->getConversation()->notes['course'] = $opts2[0];
                return $this->nextStep();
            }

        }

        $cancel = [self::CANCELAR, self::ATRAS];
        $keyboard = array_chunk($opts2, 2);
        $keyboard [] = $cancel;

        $this->getRequest()->keyboard($keyboard);

        if ($this->isProcessed() || empty($text))
        {
            return $this->getRequest()->sendMessage('Selecciona el curso al cual pertenece la asignatura:');
        }
        if (!(in_array($text, $opts2) || in_array($text, $cancel)))
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

        $this->getConversation()->notes['course'] = " ".$text;
        return $this->nextStep();
    }

    public function processShowSemesters($text)
    {
        $this->getRequest()->sendAction(Request::ACTION_TYPING);

        $selectedCourse = $this->getConversation()->notes['course'];

        $selectedPlan = $this->getConversation()->notes['plan'];

        try
        {
            $ordenadas = SubjectRepository::getSubjectsList($selectedPlan, $this->getActualYear());
        }
        catch (\Exception $exception)
        {
            if (preg_match('/Unable to connect to /',$exception->getMessage()))
            {
                $msge = "Parece que la API de la UPM esta caida.";
            }
            elseif ($exception->getMessage() == "Unable to parse response as JSON")
            {
                $msge = "Parece que la API de la UPM esta caida.";
                print("No se ha interpretado el JSON de la petición.");
                print($exception->getMessage());
                print($exception->getTraceAsString());
            }
            else
            {
                $msge = "Ocurrió un error inesperado.";
                print($msge);
                throw $exception;
            }
            $msge .= " Vuelva a intentarlo mas tarde.";
            $result = $this->getRequest()->markdown()->sendMessage($msge."\n\n");
            return $result;
        }


        $opts3 = array_keys($ordenadas[$selectedCourse]);

        $cancel = [self::CANCELAR, self::ATRAS];
        $keyboard = array_chunk($opts3, 2);
        $keyboard [] = $cancel;

        $this->getRequest()->keyboard($keyboard);
        if ($this->isProcessed() || empty($text))
        {
            return $this->getRequest()->sendMessage('Selecciona el semestre al cual pertenece la asignatura:');
        }

        if (!(in_array($text, $opts3) || in_array($text, $cancel)))
        {
            return $this->getRequest()->sendMessage('Selecciona una opción del teclado por favor:');
        }

        if (in_array($text, $cancel))
        {
            if ($text === self::CANCELAR)
            {
                return $this->cancelConversation();
            }
            else
            {
                return $this->previousStep();
            }
        }

        $this->getConversation()->notes['semester'] = $text;
        return $this->nextStep();
    }

    public function processShowSubjects($text)
    {
        $this->getRequest()->sendAction(Request::ACTION_TYPING);

        $selectedSemester = $this->getConversation()->notes['semester'];
        $selectedCourse = $this->getConversation()->notes['course'];
        $selectedPlan = $this->getConversation()->notes['plan'];

        try
        {
            $ordenadas = SubjectRepository::getSubjectsList($selectedPlan, $this->getActualYear());
        }
        catch (\Exception $exception)
        {
            if (preg_match('/Unable to connect to /',$exception->getMessage()))
            {
                $msge = "Parece que la API de la UPM esta caida.";
            }
            elseif ($exception->getMessage() == "Unable to parse response as JSON")
            {
                $msge = "Parece que la API de la UPM esta caida.";
                print("No se ha interpretado el JSON de la petición.");
                print($exception->getMessage());
                print($exception->getTraceAsString());
            }
            else
            {
                $msge = "Ocurrió un error inesperado.";
                print($msge);
                throw $exception;
            }
            $msge .= " Vuelva a intentarlo mas tarde.";
            $result = $this->getRequest()->markdown()->sendMessage($msge."\n\n");
            return $result;
        }

        $asignaturas = $ordenadas[$selectedCourse][$selectedSemester];

        foreach ($asignaturas as $asignatura)
        {
            $opts4[$asignatura->codigo] =  "$asignatura->nombre";
        }

        natcasesort($opts4);

        $cancel = [self::CANCELAR,self::ATRAS];
        $keyboard = array_chunk($opts4, 2);
        $keyboard [] = $cancel;

        $this->getRequest()->keyboard($keyboard);

        if ($this->isProcessed() || empty($text))
        {
            return $this->getRequest()->sendMessage('Selecciona la asignatura de la cual necesitas información:');
        }

        if (!(in_array($text, $opts4) || in_array($text, $cancel)))
        {
            return $this->getRequest()->sendMessage('Selecciona una opción del teclado por favor:');
        }

        if (in_array($text, $cancel))
        {
            if ($text === self::CANCELAR)
            {
                return $this->cancelConversation();
            }
            else
            {
                return $this->previousStep();
            }
        }


        $this->getConversation()->notes['subject'] = array_search($text,$opts4);
        return $this->nextStep();
    }

    public function processShowInfoSubject($text)
    {
        $this->getRequest()->sendAction(Request::ACTION_TYPING);

        $selectedCourse = $this->getConversation()->notes['course'];
        $selectedSemester = $this->getConversation()->notes['semester'];
        $selectedPlan = $selectedPlan = $this->getConversation()->notes['plan'];
        $selectedSubject = $this->getConversation()->notes['subject'];
        
        try
        {
            $subject = SubjectRepository::getSubject($selectedPlan, $selectedSubject, $selectedSemester, $this->getActualYear());
        }
        catch (\Exception $exception)
        {
            if (preg_match('/Unable to connect to /',$exception->getMessage()))
            {
                $msge = "Parece que la API de la UPM esta caida.";
            }
            elseif ($exception->getMessage() == "Unable to parse response as JSON")
            {
                $msge = "Parece que la asignatura escogida *no tiene información disponible en estos momentos*.\n" .
                    "Esto puede suceder al escoger una asignatura del semestre siguiente al actual (la cual no estan las guias " .
                    "aun redactadas), o bien al intentar acceder a una asignatura de créditos optativos, la cual no tiene guía docente.\n" .
                    "*Por favor, selecciona otra asignatura de la lista.*\n\n";
                $result = $this->getRequest()->markdown()->sendMessage($msge."\n\n");
                return $this->previousStep();
            }
            else
            {
                $msge = "Ocurrió un error inesperado.";
                print($msge);
                throw $exception;
            }
            $msge .= " Vuelva a intentarlo mas tarde.";
            $result = $this->getRequest()->markdown()->sendMessage($msge."\n\n");
            return $result;
        }

        $numProfesores = count($subject->profesores);

//        $message = "Información sobre...\n*$subject->nombre*\nDepartamento: *$subject->depto*\nTipo: *$subject->caracter*\n" .
//            "Créditos: *$subject->ects ECTS*\nProfesores: *$numProfesores profesores*\n\n" .
//            "Selecciona mediante el teclado una opción.\n";
        $message = "Información sobre...\n*$subject->nombre*\nPlan: *$subject->plan*\nAño: *$subject->anio*\n" .
            "Curso: *$selectedCourse*\nSemestre: *$subject->semestre*\nDepartamento: *$subject->depto*\nTipo: *$subject->caracter*\n" .
            "Créditos: *$subject->ects ECTS*\nProfesores: *$numProfesores profesores*\n\n" .
            "Selecciona mediante el teclado una opción.\n";


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
//        if($text == self::HORARIO)
//        {
//            return $this->nextStep('timetable');
//        }
        if($text == self::CRITERIOS) {
            return $this->nextStep('criteria');
        }
        if($text == self::ACTIVIDADES) {
            return $this->nextStep('activities');
        }
        if($text == self::RECURSOS) {
            return $this->nextStep('resources');
        }
        if($text == self::GUIA_DOCENTE) {
            return $this->nextStep('sendGuide');
        }
        if (in_array($text, $cancel))
        {
            if ($text === self::CANCELAR)
            {
                return $this->cancelConversation();
            }
            else
            {
                return $this->previousStep();
            }
        }

        return $this->getRequest()->sendMessage('Selecciona una opción del teclado por favor:');
    }

    public function processSendGuide()
    {
        $this->getRequest()->sendAction(Request::ACTION_TYPING);


        $selectedSemester = $this->getConversation()->notes['semester'];
        $selectedPlan = $this->getConversation()->notes['plan'];
        $selectedSubject = $this->getConversation()->notes['subject'];

        try
        {
            $subject = SubjectRepository::getSubject($selectedPlan, $selectedSubject, $selectedSemester, $this->getActualYear());
        }
        catch (\Exception $exception)
        {
            if (preg_match('/Unable to connect to /',$exception->getMessage()))
            {
                $msge = "Parece que la API de la UPM esta caida.";
            }
            elseif ($exception->getMessage() == "Unable to parse response as JSON")
            {
                $msge = "Parece que la asignatura escogida *no tiene información disponible en estos momentos*.\n" .
                    "Esto puede suceder al escoger una asignatura del semestre siguiente al actual (la cual no estan las guias " .
                    "aun redactadas), o bien al intentar acceder a una asignatura de créditos optativos, la cual no tiene guía docente.\n" .
                    "*Por favor, selecciona otra asignatura de la lista.*\n\n";
                $result = $this->getRequest()->markdown()->sendMessage($msge."\n\n");
                return $this->previousStep();
            }
            else
            {
                $msge = "Ocurrió un error inesperado.";
                print($msge);
                throw $exception;
            }
            $msge .= " Vuelva a intentarlo mas tarde.";
            $result = $this->getRequest()->markdown()->sendMessage($msge."\n\n");
            return $result;
        }


        // TODO: Mirar el error SSL.
        //$guiaPDF=SubjectRepository::getGuia($subject->guia);
        //$cap = "Aquí te enviamos la guia docente de $subject->nombre";
        //$this->getRequest()->caption("$cap")->sendDocument($guiaPDF);

        $mensaje = "Aquí tienes la guia docente de $subject->nombre:\n";
        $mensaje .= "$subject->guia\n";
        if ($subject->fecha_actualizacion !== null && $subject->fecha_actualizacion !== "")
        {
            $mensaje .= "Última actualización: $subject->fecha_actualizacion\n";
        }

        $result = $this->getRequest()->hideKeyboard()->sendMessage($mensaje);
        $this->stopConversation();
        return $result;
    }

    public function processTeacher($text)
    {
        $this->getRequest()->sendAction(Request::ACTION_TYPING);


        $selectedSemester = $this->getConversation()->notes['semester'];
        $selectedPlan = $this->getConversation()->notes['plan'];
        $selectedSubject = $this->getConversation()->notes['subject'];
        try
        {
            $subject = SubjectRepository::getSubject($selectedPlan, $selectedSubject, $selectedSemester, $this->getActualYear());
        }
        catch (\Exception $exception)
        {
            if (preg_match('/Unable to connect to /',$exception->getMessage()))
            {
                $msge = "Parece que la API de la UPM esta caida.";
            }
            elseif ($exception->getMessage() == "Unable to parse response as JSON")
            {
                $msge = "Parece que la asignatura escogida *no tiene información disponible en estos momentos*.\n" .
                    "Esto puede suceder al escoger una asignatura del semestre siguiente al actual (la cual no estan las guias " .
                    "aun redactadas), o bien al intentar acceder a una asignatura de créditos optativos, la cual no tiene guía docente.\n" .
                    "*Por favor, selecciona otra asignatura de la lista.*\n\n";
                $result = $this->getRequest()->markdown()->sendMessage($msge."\n\n");
                return $this->previousStep();
            }
            else
            {
                $msge = "Ocurrió un error inesperado.";
                print($msge);
                throw $exception;
            }
            $msge .= " Vuelva a intentarlo mas tarde.";
            $result = $this->getRequest()->markdown()->sendMessage($msge."\n\n");
            return $result;
        }

        $profesoresKB = [];

        if (count($subject->profesores) !== 0)
        {
            $mensaje = "Los siguientes profesores pueden ayudarte con *$subject->nombre*:\n\n";

            foreach ($subject->profesores as $profesor)
            {
                $mensaje .= "- ";
                # nombre and apellidos are required
                $mensaje .= "*$profesor->nombre $profesor->apellidos*";
                if ($profesor->coordinador == true)
                {
                    $mensaje .= " (coordinador)";
                }
                if ($profesor->despacho !== null && $profesor->despacho !== "")
                {
                    $mensaje .= " ($profesor->despacho)";
                }
                $mensaje .= "\n";
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

        if (in_array($text, $cancel))
        {
            if ($text === self::CANCELAR)
            {
                return $this->cancelConversation();
            }
            else
            {
                return $this->previousStep();
            }
        }

        $this->getConversation()->notes['teacher'] = $text;
        return $this->nextStep();

    }

    public function processTeacherInfo($text)
    {

        $this->getRequest()->sendAction(Request::ACTION_TYPING);

        $selectedSemester = $this->getConversation()->notes['semester'];
        $selectedPlan = $this->getConversation()->notes['plan'];
        $selectedSubject = $this->getConversation()->notes['subject'];
        $selectedTeacher = $this->getConversation()->notes['teacher'];

        $mailIcon = "\xF0\x9F\x93\xA7";
        $departmentIcon = "\xF0\x9F\x91\x94";
        $clockIcon = "\xF0\x9F\x95\x92";
        $alertIcon = "\xE2\x9A\xA0";

        try
        {
            $subject = SubjectRepository::getSubject($selectedPlan, $selectedSubject, $selectedSemester, $this->getActualYear());
        }
        catch (\Exception $exception)
        {
            if (preg_match('/Unable to connect to /',$exception->getMessage()))
            {
                $msge = "Parece que la API de la UPM esta caida.";
            }
            elseif ($exception->getMessage() == "Unable to parse response as JSON")
            {
                $msge = "Parece que la asignatura escogida *no tiene información disponible en estos momentos*.\n" .
                    "Esto puede suceder al escoger una asignatura del semestre siguiente al actual (la cual no estan las guias " .
                    "aun redactadas), o bien al intentar acceder a una asignatura de créditos optativos, la cual no tiene guía docente.\n" .
                    "*Por favor, selecciona otra asignatura de la lista.*\n\n";
                $result = $this->getRequest()->markdown()->sendMessage($msge."\n\n");
                return $this->previousStep();
            }
            else
            {
                $msge = "Ocurrió un error inesperado.";
                print($msge);
                throw $exception;
            }
            $msge .= " Vuelva a intentarlo mas tarde.";
            $result = $this->getRequest()->markdown()->sendMessage($msge."\n\n");
            return $result;
        }

        $cancel = [self::CANCELAR, self::ATRAS];
        $keyboard [] = $cancel;

        if ($text === self::CANCELAR)
        {
            return $this->cancelConversation();
        }
        if ($text === self::ATRAS)
        {
            return $this->previousStep();
        }

        if ($this->isProcessed() || empty($text))
        {
            $mensaje = "No hay ningún profesor segun la api.";
            foreach ($subject->profesores as $profesor)
            {
                if (("$profesor->nombre $profesor->apellidos") == $selectedTeacher)
                {
                    $mensaje = "Información sobre...\n";
                    $mensaje .= "*$profesor->nombre $profesor->apellidos*\n";

                    if ($profesor->email !== null && $profesor->email !== "") {
                        $mensaje .= "$mailIcon Email: $profesor->email\n";
                    }
                    if ($profesor->despacho !== null && $profesor->despacho !== "") {
                        $mensaje .= "$departmentIcon Despacho: *$profesor->despacho*\n";
                    }

                    if (count($profesor->tutorias) !== 0)
                    {
                        $mensaje .= "\n$clockIcon Horarios de tutorías:\n";
                        foreach ($profesor->tutorias as $tutoria)
                        {
                            $mensaje .= $tutoria->getTutoriaMessage() . "\n";
                        }

                    }
                    else
                    {
                        $mensaje .="\n$alertIcon *El profesor no ha especificado un horario de tutorías válido.*\n".
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

    public function processCriteria($text)
    {
        $this->getRequest()->sendAction(Request::ACTION_TYPING);
        $this->getRequest()->sendMessage('NOT IMPLEMENTED');
        return $this->previousStep();
    }

    public function processActivities($text)
    {
        $this->getRequest()->sendAction(Request::ACTION_TYPING);
        $this->getRequest()->sendMessage('NOT IMPLEMENTED');
        return $this->previousStep();
    }

    public function processResources($text)
    {
        $this->getRequest()->sendAction(Request::ACTION_TYPING);
        $this->getRequest()->sendMessage('NOT IMPLEMENTED');
        return $this->previousStep();
    }

}