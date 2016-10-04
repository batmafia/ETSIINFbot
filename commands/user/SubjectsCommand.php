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
        $keyboard = [array_keys($this->planes), $cancel];

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

        $cancel = ['Cancelar'];
        $keyboard = [$opts2, $cancel];

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
            return $this->cancelConversation();
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

        $cancel = ['Cancelar'];
        $keyboard = [$opts3, $cancel];

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
            return $this->cancelConversation();
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

        $cancel = ['Cancelar'];
        $keyboard = [$opts4, $cancel];

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
            return $this->cancelConversation();
        }


        $this->getConversation()->notes['subject'] = array_search($text,$this->subjectlist);
        return $this->nextStep();
    }

    public function processShowInfoSubject($text)
    {

        $selectedSemester = $this->getConversation()->notes['semester'];
        $selectedPlan = $this->planes[$this->getConversation()->notes['plan']];
        $selectedSubject = $this->getConversation()->notes['subject'];

        $subject=SubjectRepository::getSubject($selectedPlan,$selectedSubject,$selectedSemester,"2016-17");
        $numProfesores = count($subject->profesores);

        $message = "La asignatura *$subject->nombre* pertenece al departamento de *$subject->depto*, ".
            "tiene un peso de *$subject->ects ects* y tienes a *$numProfesores profesores* dispuestos a ayudarte. ".
            "Selecciona mediante el teclado una opción.\n";


        $cancel = ['Cancelar'];
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
            return $this->cancelConversation();
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
        $mensaje ="";

        if ($this->isProcessed() || empty($text))
        {
            if ($extraInfo == self::GUIA_DOCENTE){

                $cap = "Aquí te enviamos la guia docente de $subject->nombre";
                $this->getRequest()->caption("$cap")->sendDocument($subject->guia);
                return $this->stopConversation();


            } else if ($extraInfo == self::PROFESORES){

                foreach ($subject->profesores as $profesor){
                    $mensaje .= "$profesor->nombre "."$profesor->apellido\n";
                }
                $this->getRequest()->markdown()->sendMessage($mensaje);
                return $this->stopConversation();

            }

        }

        return $this->stopConversation();
    }







    private function cancelConversation()
    {
        $msgCancel = "*Comando cancelado.*";
        $msgHelp = "Más comandos en /help.";
        $msgCancelConver = $msgCancel."\n".$msgHelp;
        $result = $this->getRequest()->hideKeyboard()->markdown()->sendMessage($msgCancelConver);
        $this->stopConversation();
        return $result;
    }
}