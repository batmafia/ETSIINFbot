<?php
/**
 * Created by PhpStorm.
 * User: Diego
 * Date: 17/10/16
 * Time: 9:36
 */

namespace app\commands\user;
use app\commands\base\Request;
use app\commands\base\BaseUserCommand;
use app\models\repositories\DirectoryRepository;

/**
 * User "/directorio" command
 */
class DirectorioCommand extends BaseUserCommand
{
    /**
     * {@inheritdoc}
     */
    public $enabled = true;
    protected $name = 'directorio';
    protected $description = 'Busca información básica sobre el personal de la universidad.';
    protected $usage = '/directorio';
    protected $version = '0.1.0';
    protected $need_mysql = true;

    const CANCELAR = 'Cancelar';
    const ATRAS = 'Atrás';
    const NUEVA_BUSQUEDA = 'Nueva Búsqueda';


    public function processGetTextForSearch($text)
    {
        $this->getRequest()->sendAction(Request::ACTION_TYPING);
        $this->getConversation();

        $keyboard [] = [self::CANCELAR];

        $this->getRequest()->keyboard($keyboard);
        if ($this->isProcessed() || empty($text))
        {
            return $this->getRequest()->markdown()->sendMessage("Introduce el nombre del personal/profesor del que deseas buscar información:");
        }
        if (in_array($text, self::CANCELAR))
        {
            return $this->cancelConversation();
        }

        $this->getConversation()->notes['text'] = $text;
        return $this->nextStep();
    }

    public function processReturnSearch($text)
    {

        $this->getRequest()->sendAction(Request::ACTION_TYPING);

        $textForSearch = $this->getConversation()->notes['text'];
        $directory = DirectoryRepository::getDirectoryInfo($textForSearch);

        $cancel = [self::CANCELAR, self::NUEVA_BUSQUEDA];
        $keyboard [] = $cancel;

        $this->getRequest()->keyboard($keyboard);

        if ($this->isProcessed() || empty($text))
        {
            return $this->getRequest()->sendMessage('Selecciona de quién deseas obtener más información:');
        }


        $this->getConversation()->notes['course'] = " ".$text;
        return $this->nextStep();
    }

    public function processShowSemesters($text)
    {
        $this->getRequest()->sendAction(Request::ACTION_TYPING);

        $selectedCourse = $this->getConversation()->notes['course'];

        $selectedPlan = $this->getConversation()->notes['plan'];
        $ordenadas = SubjectRepository::getSubjectsList($selectedPlan, $this->getActualYear());

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
        $ordenadas = SubjectRepository::getSubjectsList($selectedPlan, $this->getActualYear());
        $asignaturas = $ordenadas[$selectedCourse][$selectedSemester];

        foreach ($asignaturas as $asignatura)
        {
            $opts4[$asignatura->codigo] =  "$asignatura->nombre";
        }

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

        $message = "La asignatura *$subject->nombre ($subject->caracter)* pertenece al departamento de *$subject->depto*, " .
            "tiene un peso de *$subject->ects ECTS* y tienes a *$numProfesores profesores* dispuestos a ayudarte.\n" .
            "Selecciona mediante el teclado una opción.\n";


        $cancel = [self::CANCELAR, self::ATRAS];
        $keyboard = [[self::GUIA_DOCENTE], [self::PROFESORES], $cancel];
        $this->getRequest()->keyboard($keyboard);
        if($this->isProcessed() || empty($text))
        {
            return $this->getRequest()->markdown()->sendMessage($message);
        }
        if($text == self::GUIA_DOCENTE) {
            return $this->nextStep('sendGuide');
        }
        if($text == self::PROFESORES)
        {
            return $this->nextStep('teacher');
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

        $subject = SubjectRepository::getSubject($selectedPlan, $selectedSubject, $selectedSemester, $this->getActualYear());

        // TODO: Mirar el error SSL.
        //$guiaPDF=SubjectRepository::getGuia($subject->guia);
        //$cap = "Aquí te enviamos la guia docente de $subject->nombre";
        //$this->getRequest()->caption("$cap")->sendDocument($guiaPDF);
        $result = $this->getRequest()->hideKeyboard()->sendMessage("Aquí tienes la guia docente de $subject->nombre\n$subject->guia");
        $this->stopConversation();
        return $result;
    }

    public function processTeacher($text)
    {
        $this->getRequest()->sendAction(Request::ACTION_TYPING);


        $selectedSemester = $this->getConversation()->notes['semester'];
        $selectedPlan = $this->getConversation()->notes['plan'];
        $selectedSubject = $this->getConversation()->notes['subject'];
        $subject = SubjectRepository::getSubject($selectedPlan, $selectedSubject, $selectedSemester, $this->getActualYear());

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

        $subject = SubjectRepository::getSubject($selectedPlan, $selectedSubject, $selectedSemester, $this->getActualYear());

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

        $result = $this->getRequest()->markdown()->hideKeyboard()->sendMessage($mensaje);
        $this->stopConversation();
        return $result;
    }

}