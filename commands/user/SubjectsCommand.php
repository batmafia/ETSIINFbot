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

    private $planes = [
        self::ING_INF => '10II',
        self::ING_MATEINF => '10MI',
        self::INF_DOBGRA => '10ID'
    ];


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





    public function processSelectCourse($text)
    {
        $selectedPlan = $this->getConversation()->notes['plan'];

        // TODO: Obtain the $anio from other api.
        $subjectsOfPlan=SubjectRepository::getSubjectsList($this->planes[$selectedPlan],'201617');

        $availableCourses=[];
        foreach ($subjectsOfPlan as $subjectCod => $subject)
        {
            if (!in_array($subject->curso,$availableCourses))
            {
                $availableCourses[$subject->curso]=[];
            }
            $availableCourses[$subject->curso]=$subjectCod;
        }

        print_r($availableCourses);


        $cancel = ['Cancelar'];
        $keyboard = [$availableCourses, $cancel];

        $this->getRequest()->keyboard($keyboard);
        if ($this->isProcessed() || empty($text))
        {
            return $this->getRequest()->sendMessage('Selecciona el curso al cual pertenece la asignatura:');
        }
        if( !(in_array($text, $availableCourses) || in_array($text, $cancel)) )
        {
            return $this->getRequest()->sendMessage('Selecciona una opción del teclado por favor:');
        }
        if (in_array($text, $cancel))
        {
            return $this->cancelConversation();
        }

        $this->getConversation()->notes['course'] = $text;
        return $this->nextStep();
        $this->stopConversation();
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