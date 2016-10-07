<?php
/**
 * Created by PhpStorm.
 * User: Diego
 * Date: 30/9/16
 * Time: 13:01
 */

namespace app\commands\user;
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
    protected $version = '0.1.0';
    protected $need_mysql = true;


    const ING_INF = 'Grado en Ingeniería Informática';
    const ING_MATEINF = 'Grado en Matemáticas e Informática';
    const INF_DOBGRA = 'Doble Grado en Ingeniería Informática y en ADE';

    const PROFESORES = 'Profesores y Tutorías';
    const GUIA_DOCENTE = 'Guía Docente';

    const CANCELAR = 'Cancelar';
    const ATRAS = 'Atrás';

    private $planes = [
        self::ING_INF => '10II',
        self::ING_MATEINF => '10MI',
        self::INF_DOBGRA => '10ID'
    ];

    public $subjectlist = [];


    private function getActualYear($apiOption)
    {

        $year = intval(date("Y"));
        $year2 = intval(date("y"));

        if (intval(date("m")) > 7)
        {
            $year2=$year2+1;
        }
        else
        {
            $year=$year-1;
        }

        if ($apiOption===0)
        {
            return "$year$year2";
        }
        else if ($apiOption===1)
        {
            return "$year-$year2";
        }


    }

    public function processGetPlan($text)
    {
        $this->getConversation();

        $cancel = [self::CANCELAR];
        $keyboard = array_chunk(array_keys($this->planes), 1);
        $keyboard [] = [self::CANCELAR];


        $this->getRequest()->keyboard($keyboard);
        if ($this->isProcessed() || empty($text))
        {
            return $this->getRequest()->sendMessage('Selecciona tu plan de estudios:');
        }
        if (!(in_array($text, array_keys($this->planes)) || in_array($text, $cancel)))
        {
            return $this->getRequest()->sendMessage('Selecciona una opción del teclado por favor:');
        }
        if (in_array($text, $cancel))
        {
            return $this->cancelConversation();
        }
        $this->getConversation()->notes['plan'] = $text;
        return $this->nextStep();
    }

    public function processShowCourse($text)
    {
        $selectedPlan = $this->getConversation()->notes['plan'];
        $ordenadas = SubjectRepository::getSubjectsList($this->planes[$selectedPlan], $this->getActualYear(0));

        $opts2 = array_keys($ordenadas);

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

        $this->getConversation()->notes['course'] = " ".$text;
        return $this->nextStep();
    }

    public function processShowSemesters($text)
    {
        $selectedCourse = $this->getConversation()->notes['course'];

        $selectedPlan = $this->getConversation()->notes['plan'];
        $ordenadas = SubjectRepository::getSubjectsList($this->planes[$selectedPlan], $this->getActualYear(0));

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

        $selectedSemester = $this->getConversation()->notes['semester'];
        $selectedCourse = $this->getConversation()->notes['course'];
        $selectedPlan = $this->getConversation()->notes['plan'];
        $ordenadas = SubjectRepository::getSubjectsList($this->planes[$selectedPlan], $this->getActualYear(0));
        $asignaturas = $ordenadas[$selectedCourse][$selectedSemester];

        $opts4 = array_keys($asignaturas);

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


        $this->getConversation()->notes['subject'] = $asignaturas[$text]->codigo;
        return $this->nextStep();
    }

    public function processShowInfoSubject($text)
    {

        $selectedSemester = $this->getConversation()->notes['semester'];
        $selectedPlan = $this->planes[$this->getConversation()->notes['plan']];
        $selectedSubject = $this->getConversation()->notes['subject'];

        try
        {
            $subject = SubjectRepository::getSubject($selectedPlan, $selectedSubject, $selectedSemester, $this->getActualYear(1));
        }
        catch (\Exception $exception)
        {
            if ($exception->getMessage() == "Unable to parse response as JSON")
            {
                $this->getRequest()->markdown()->sendMessage("Parece que la asignatura escogida *no tiene información disponible en estos momentos*.\n" .
                    "Esto puede suceder al escoger una asignatura del semestre siguiente al actual (la cual no estan las guias " .
                    "aun redactadas), o bien al intentar acceder a una asignatura de créditos optativos, la cual no tiene guía docente.\n" .
                    "*Por favor, selecciona otra asignatura de la lista.*\n\n");

                return $this->previousStep();
            }
            else
            {
                throw $exception;
            }
        }

        $numProfesores = count($subject->profesores);

        $message = "La asignatura *$subject->nombre* pertenece al departamento de *$subject->depto*, " .
            "tiene un peso de *$subject->ects ECTS* y tienes a *$numProfesores profesores* dispuestos a ayudarte. " .
            "Selecciona mediante el teclado una opción.\n";


        $cancel = [self::CANCELAR, self::ATRAS];
        $keyboard = [[self::GUIA_DOCENTE], [self::PROFESORES], $cancel];
        $this->getRequest()->keyboard($keyboard);
        if ($this->isProcessed() || empty($text))
        {
            return $this->getRequest()->markdown()->sendMessage($message);
        }
        if (!($text == self::GUIA_DOCENTE || $text == self::PROFESORES || in_array($text, $cancel)))
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

        $this->getConversation()->notes['extrainfo'] = $text;
        return $this->nextStep();
    }

    public function processShowExtraInfo($text)
    {

        $selectedSemester = $this->getConversation()->notes['semester'];
        $selectedPlan = $this->planes[$this->getConversation()->notes['plan']];
        $selectedSubject = $this->getConversation()->notes['subject'];
        $extraInfo = $this->getConversation()->notes['extrainfo'];

        $subject = SubjectRepository::getSubject($selectedPlan, $selectedSubject, $selectedSemester, $this->getActualYear(1));

        if ($this->isProcessed() || empty($text))
        {
            if ($extraInfo == self::GUIA_DOCENTE)
            {

                // TODO: Mirar el error SSL.
                //$guiaPDF=SubjectRepository::getGuia($subject->guia);
                //$cap = "Aquí te enviamos la guia docente de $subject->nombre";
                //$this->getRequest()->caption("$cap")->sendDocument($guiaPDF);
                $this->getRequest()->hideKeyboard()->sendMessage("Aquí tienes la guia docente de $subject->nombre\n$subject->guia");
                return $this->stopConversation();


            }
            else if ($extraInfo == self::PROFESORES)
            {
                return $this->nextStep();
            }
        }
    }

    public function processGetTeacher($text)
    {

        $selectedSemester = $this->getConversation()->notes['semester'];
        $selectedPlan = $this->planes[$this->getConversation()->notes['plan']];
        $selectedSubject = $this->getConversation()->notes['subject'];
        $subject = SubjectRepository::getSubject($selectedPlan, $selectedSubject, $selectedSemester, $this->getActualYear(1));

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
            $this->getRequest()->markdown()->sendMessage($mensaje);
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

    public function processShowTeacherInfo($text)
    {

        $selectedSemester = $this->getConversation()->notes['semester'];
        $selectedPlan = $this->planes[$this->getConversation()->notes['plan']];
        $selectedSubject = $this->getConversation()->notes['subject'];
        $selectedTeacher = $this->getConversation()->notes['teacher'];

        $subject = SubjectRepository::getSubject($selectedPlan, $selectedSubject, $selectedSemester, $this->getActualYear(1));

        if ($this->isProcessed() || empty($text))
        {
            foreach ($subject->profesores as $profesor)
            {
                if (("$profesor->nombre $profesor->apellidos") == $selectedTeacher)
                {
                    $mensaje = "El profesor *$profesor->nombre $profesor->apellidos* te puede atender personalmente " .
                        "en su despacho *$profesor->despacho* o bien vía email en la dirección " .
                        "$profesor->email .\n";

                    if (count($profesor->tutorias) !== 0)
                    {
                        $mensaje .= "Sus horarios de tutorias son:\n";
                        foreach ($profesor->tutorias as $tutoria)
                        {
                            $mensaje .= $tutoria->getTutoriaMessage() . "\n";
                        }

                    }
                    else
                    {
                        $mensaje .="*El profesor no ha especificado un horario de tutorias válido.*\n".
                            "Si tienes alguna duda ponte en contacto vía email.";
                    }
                }
            }
        }

        $this->getRequest()->markdown()->hideKeyboard()->sendMessage($mensaje);
        return $this->stopConversation();
    }

}