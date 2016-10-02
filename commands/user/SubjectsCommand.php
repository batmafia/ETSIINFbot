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
    const UNKNOWN = 'Sin Especificar';

    private $planes = [
        self::ING_INF => '10II',
        self::ING_MATEINF => '10MI',
        self::INF_DOBGRA => '10ID'
    ];

    public $ordenadas = [];
    public $porsemestre = [];


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
                $this->ordenadas[self::UNKNOWN][] = $s;
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
                    $this->porsemestre[self::UNKNOWN][] = $sub;
                }
            }
        }

        foreach ($this->porsemestre as $k=>$value)
        {
            echo "\nAhora printeamos asignaturas del $k semestre:\n";
            foreach ($value as $p){
                echo $p->nombre."\n";
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
        echo $this->getConversation()->notes['semester'] = $text;
        return $this->nextStep();
    }


    public function processShowSubjects($text)
    {
        $selectedSemester = $this->getConversation()->notes['semester'];

        foreach($this->porsemestre[$selectedSemester] as $subject){
            echo $subject->nombre."\n";
        }

        $cancel = ['Cancelar'];
        $keyboard = ['holi', $cancel];

        $this->getRequest()->keyboard($keyboard);
        if ($this->isProcessed() || empty($text))
        {
            return $this->getRequest()->sendMessage('Selecciona el semestre al cual pertenece la asignatura:');
        }
        if( !(in_array($text, 'holi') || in_array($text, $cancel)) )
        {
            return $this->getRequest()->sendMessage('Selecciona una opción del teclado por favor:');
        }
        if (in_array($text, $cancel))
        {
            return $this->cancelConversation();
        }

        $this->getConversation()->notes['subject'] = $text;
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