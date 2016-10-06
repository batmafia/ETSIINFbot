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
use yii\base\Exception;

/**
 * User "/subjects" command
 */
class SubjectsCommand extends BaseUserCommand
{
    /**
     * {@inheritdoc}
     */
    public $enabled = true;
    protected $name = 'subjects';
    protected $description = 'Consulta información sobre las asignaturas, sus profesores y las tutorias.';
    protected $usage = '/subjects';
    protected $version = '0.1.0';
    protected $need_mysql = true;


    const ING_INF = 'GRADO EN INGENIERIA INFORMATICA';
    const ING_MATEINF = 'GRADO EN MATEMATICAS E INFORMATICA';
    const INF_DOBGRA = 'DOBLE GRADO EN INGENIERIA INFORMATICA Y EN ADE';


    const PROFESORES = 'Profesores y Tutorías';
    const GUIA_DOCENTE='Guía Docente';

    private $planes = [
        self::ING_INF => '10II',
        self::ING_MATEINF => '10MI',
        self::INF_DOBGRA => '10ID'
    ];

    public $ordenadas = [];
    public $porsemestre = [];
    public $subjectlist = [];


    public function processGetPlan($text)
    {
        $this->getConversation();

        $cancel = ['Cancelar'];

        $keyboard = array_chunk(array_keys($this->planes), 1);
        $keyboard [] = $cancel;


        $this->getRequest()->keyboard($keyboard);
        if ( $this->isProcessed() || empty($text) )
        {
            return $this->getRequest()->sendMessage('Selecciona tu plan de estudios:');
        }
        if( !(in_array($text, array_keys($this->planes)) || in_array($text, $cancel)) )
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
        $subjectsOfPlan=SubjectRepository::getSubjectsList($this->planes[$selectedPlan],'201617');

        foreach($subjectsOfPlan as $s)
        {
            if ($s->curso !== "")
            {
                $this->ordenadas[$s->curso][] = $s;
            }
            else
            {
                // NO METEMOS LAS QUE NO TENGAN CURSO
                //$this->ordenadas[self::UNKNOWN][] = $s;
            }
        }

        // Courses for the keyboard.
        $opts2 =[];
        ksort($this->ordenadas);

        foreach ($this->ordenadas as $key=>$k)
        {
            $opts2[]="$key";
        }



        $cancel = ['Cancelar','Atrás'];
        $keyboard = array_chunk($opts2, 2);
        $keyboard [] = $cancel;

        $this->getRequest()->keyboard($keyboard);
        if ($this->isProcessed() || empty($text))
        {
            return $this->getRequest()->sendMessage('Selecciona el curso al cual pertenece la asignatura:');
        }
        if( !(in_array($text, $opts2) || in_array($text, $cancel)) )
        {
            return $this->getRequest()->sendMessage('Selecciona una opción del teclado por favor:');
        }
        if (in_array($text, $cancel))
        {
            if ($text === "Cancelar")
            {
                return $this->cancelConversation();
            }
            else
            {
                return $this->previousStep();
            }
        }

        $this->getConversation()->notes['course'] = $text;
        return $this->nextStep();
    }


    public function processShowSemesters($text)
    {
        $selectedCourse = $this->getConversation()->notes['course'];

        $selectedPlan = $this->getConversation()->notes['plan'];
        $subjectsOfPlan=SubjectRepository::getSubjectsList($this->planes[$selectedPlan],'201617');

        foreach($subjectsOfPlan as $s)
        {
            if ($s->curso !== "")
            {
                $this->ordenadas[$s->curso][] = $s;
            }
            else
            {
                // NO METEMOS LAS QUE NO TENGAN CURSO
               // $this->ordenadas[self::UNKNOWN][] = $s;
            }
        }


        foreach($this->ordenadas[$selectedCourse] as $sub)
        {

            foreach($sub->imparticion as $sem)
            {

                if ($sem->codigo_duracion !== "")
                {
                    $this->porsemestre[$sem->codigo_duracion][] = $sub;
                }
                else
                {
                    // NO METEMOS LAS QUE NO TENGAN SEMESTRE
                    // $this->porsemestre[self::UNKNOWN][] = $sub;
                }
            }
        }



        $opts3 =[];
        ksort($this->porsemestre);

        foreach ($this->porsemestre as $key=>$k)
        {
            $opts3[]="$key";
        }

        $cancel = ['Cancelar','Atrás'];
        $keyboard = array_chunk($opts3, 2);
        $keyboard [] = $cancel;

        $this->getRequest()->keyboard($keyboard);
        if ($this->isProcessed() || empty($text))
        {
            return $this->getRequest()->sendMessage('Selecciona el semestre al cual pertenece la asignatura:');
        }
        if( !(in_array($text, $opts3) || in_array($text, $cancel)) )
        {
            return $this->getRequest()->sendMessage('Selecciona una opción del teclado por favor:');
        }
        if (in_array($text, $cancel))
        {
            if ($text === "Cancelar")
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
        $subjectsOfPlan=SubjectRepository::getSubjectsList($this->planes[$selectedPlan],'201617');

        foreach($subjectsOfPlan as $s)
        {
            if ($s->curso !== "")
            {
                $this->ordenadas[$s->curso][] = $s;
            }
            else
            {
                // NO METEMOS LAS QUE NO TENGAN CURSO
                // $this->ordenadas[self::UNKNOWN][] = $s;
            }
        }


        foreach($this->ordenadas[$selectedCourse] as $sub)
        {

            foreach($sub->imparticion as $sem)
            {

                if ($sem->codigo_duracion !== "")
                {
                    $this->porsemestre[$sem->codigo_duracion][] = $sub;
                }
                else
                {
                    // NO METEMOS LAS QUE NO TENGAN SEMESTRE
                    // $this->porsemestre[self::UNKNOWN][] = $sub;
                }
            }
        }

        foreach($this->porsemestre[$selectedSemester] as $sub)
        {
            $this->subjectlist[$sub->codigo]=$sub->nombre;
        }

        foreach ($this->subjectlist as $key=>$k)
        {
            $opts4[]="$k";
        }

        $cancel = ['Cancelar','Atrás'];
        $keyboard = array_chunk($opts4, 2);
        $keyboard [] = $cancel;

        $this->getRequest()->keyboard($keyboard);
        if ($this->isProcessed() || empty($text))
        {
            return $this->getRequest()->sendMessage('Selecciona la asignatura de la cual necesitas información:');
        }
        if( !(in_array($text, $opts4) || in_array($text, $cancel)) )
        {
            return $this->getRequest()->sendMessage('Selecciona una opción del teclado por favor:');
        }
        if (in_array($text, $cancel))
        {
            if ($text === "Cancelar")
            {
                return $this->cancelConversation();
            }
            else
            {
                return $this->previousStep();
            }
        }


        $this->getConversation()->notes['subject'] = array_search($text,$this->subjectlist);
        return $this->nextStep();
    }

    public function processShowInfoSubject($text)
    {

        $selectedSemester = $this->getConversation()->notes['semester'];
        $selectedPlan = $this->planes[$this->getConversation()->notes['plan']];
        $selectedSubject = $this->getConversation()->notes['subject'];

        try {

            $subject=SubjectRepository::getSubject($selectedPlan,$selectedSubject,$selectedSemester,"2016-17");

        } catch (\Exception $exception){

            if($exception->getMessage() == "Unable to parse response as JSON")
            {
                $this->getRequest()->markdown()->sendMessage("Parece que la asignatura escogida *no tiene información disponible en estos momentos*.\n".
                    "Esto puede suceder al escoger una asignatura del semestre siguiente al actual (la cual no estan las guias ".
                    "aun redactadas), o bien al intentar acceder a una asignatura de créditos optativos, la cual no tiene guía docente.\n".
                    "*Por favor, selecciona otra asignatura de la lista.*\n\n");

                return $this->previousStep();
            }
            else
            {
                throw $exception;
            }

        }

        $numProfesores = count($subject->profesores);

        $message = "La asignatura *$subject->nombre* pertenece al departamento de *$subject->depto*, ".
            "tiene un peso de *$subject->ects ects* y tienes a *$numProfesores profesores* dispuestos a ayudarte. ".
            "Selecciona mediante el teclado una opción.\n";


        $cancel = ['Cancelar','Atrás'];
        $keyboard = [[self::GUIA_DOCENTE],[self::PROFESORES], $cancel];
        $this->getRequest()->keyboard($keyboard);
        if ($this->isProcessed() || empty($text))
        {
            return $this->getRequest()->markdown()->sendMessage($message);
        }
        if( !($text == self::GUIA_DOCENTE || $text== self::PROFESORES || in_array($text, $cancel)) )
        {
            return $this->getRequest()->sendMessage('Selecciona una opción del teclado por favor:');
        }
        if (in_array($text, $cancel))
        {
            if ($text === "Cancelar")
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

        $subject=SubjectRepository::getSubject($selectedPlan,$selectedSubject,$selectedSemester,"2016-17");
        if ($this->isProcessed() || empty($text))
        {
            if ($extraInfo == self::GUIA_DOCENTE){

                // TODO: Mirar el error SSL.
                //$guiaPDF=SubjectRepository::getGuia($subject->guia);
                //$cap = "Aquí te enviamos la guia docente de $subject->nombre";
                //$this->getRequest()->caption("$cap")->sendDocument($guiaPDF);
                $this->getRequest()->hideKeyboard()->sendMessage("Aquí tienes la guia docente de $subject->nombre\n$subject->guia");
                return $this->stopConversation();


            } else if ($extraInfo == self::PROFESORES){

                return $this->nextStep();
            }

        }

    }

    public function processGetTeacher($text){

        $selectedSemester = $this->getConversation()->notes['semester'];
        $selectedPlan = $this->planes[$this->getConversation()->notes['plan']];
        $selectedSubject = $this->getConversation()->notes['subject'];
        $subject=SubjectRepository::getSubject($selectedPlan,$selectedSubject,$selectedSemester,"2016-17");

        $numProfesores = count($subject->profesores);
        $profesoresKB = [];

        if ($numProfesores!==0){
            $mensaje = "Los siguientes profesores pueden ayudarte con $subject->nombre:\n\n";
            foreach ($subject->profesores as $profesor){
                if($profesor->coordinador == true){
                    $mensaje.= "- *$profesor->nombre $profesor->apellidos* (coordinador)\n";
                }else{
                    $mensaje.= "- *$profesor->nombre $profesor->apellidos*\n";
                }

                $profesoresKB[] = "$profesor->nombre $profesor->apellidos";

            }
            $mensaje .= "\n¿De qué profesor deseas obtener más información?";
        }else{
            $mensaje = "No hay ningún profesor asignado.";
        }

        $cancel = ['Cancelar','Atrás'];
        $keyboard = array_chunk($profesoresKB, 2);
        $keyboard [] = $cancel;

        $this->getRequest()->keyboard($keyboard);

        if ($this->isProcessed() || empty($text))
        {
            $this->getRequest()->markdown()->sendMessage($mensaje);
        }
        if( !(in_array($text, $profesoresKB) || in_array($text, $cancel)) )
        {
            return $this->getRequest()->sendMessage('Selecciona una opción del teclado por favor:');
        }
        if (in_array($text, $cancel))
        {
            if ($text === "Cancelar")
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

        $subject=SubjectRepository::getSubject($selectedPlan,$selectedSubject,$selectedSemester,"2016-17");

        if ($this->isProcessed() || empty($text))
        {
            foreach ($subject->profesores as $profesor){
                if(("$profesor->nombre $profesor->apellidos")==$selectedTeacher){

                    $mensaje = "El profesor *$profesor->nombre $profesor->apellidos* te puede atender personalmente ".
                    "en su despacho *$profesor->despacho* o bien vía email en la dirección ".
                    "$profesor->email .\n";

                    if (count($profesor->tutorias !== 0)){
                        $mensaje .= "Sus horarios de tutorias son:\n";
                        foreach ($profesor->tutorias as $tutoria){
                            $mensaje .=$tutoria->getTutoriaMessage()."\n";
                        }
                    }


                    $this->getRequest()->hideKeyboard()->markdown()->sendMessage($mensaje);
                    return $this->stopConversation();

                }
            }

        }



        return $this->stopConversation();
    }



}