<?php
/**
 * Created by PhpStorm.
 * User: frildoren
 * Date: 14/10/16
 * Time: 23:36
 */

namespace app\commands\user;


use app\commands\base\BaseUserCommand;
use app\models\repositories\CalendarRepository;

class CalendariosCommand extends BaseUserCommand
{

    public $enabled = true;

    protected $name = 'calendarios';
    protected $description = 'Envía calendarios escolares y horarios de los grupos.';
    protected $usage = '/calendarios';
    protected $version = '0.1.0';
    protected $need_mysql = true;
    
    
    const TIMETABLES = "Horarios";
    const BUSINESS_CALENDAR = "Calendario escolar";
    const EXAM_CALENDAR = "Calendario de exámenes";
    
    const CANCEL = 'Cancelar';
    
    public function processPickType($text)
    {
        $opts = [self::TIMETABLES, self::BUSINESS_CALENDAR, self::EXAM_CALENDAR];
        $keyboard = array_chunk($opts, 1);
        $keyboard[] = [self::CANCEL];

        $this->getConversation();

        if($text === self::BUSINESS_CALENDAR)
        {
            return $this->nextStep('businessCalendar');
        }
        if($text === self::TIMETABLES || $text === self::EXAM_CALENDAR)
        {
            $this->getConversation()->notes['calendar'] = $text;
            return $this->nextStep();
        }
        if ($text === self::CANCEL)
        {
            return $this->cancelConversation();
        }

        $this->getRequest()->keyboard($keyboard);
        if($this->isProcessed() || empty($text))
        {
            return $this->getRequest()->sendMessage("Selecciona el tipo de calendario");
        }

        return $this->getRequest()->sendMessage("Selecciona una opción del teclado por favor");

    }

    public function processPickDegree($text)
    {
        $degrees = CalendarRepository::getDegrees();
        $keyboard = array_chunk(array_keys($degrees), 1);
        $keyboard[] = [self::CANCEL];

        $this->getRequest()->keyboard($keyboard);

        if($this->isProcessed() || empty($text))
        {
            return $this->getRequest()->sendMessage("Selecciona el plan de estudios");
        }
        if($text === self::CANCEL)
        {
            return $this->cancelConversation();
        }
        if( !(key_exists($text, $degrees)) )
        {
            return $this->getRequest()->sendMessage("Selecciona una opción del teclado por favor");
        }

        $this->getConversation()->notes['degree'] = $degrees[$text];

        if($this->getConversation()->notes['calendar'] === self::TIMETABLES)
            return $this->nextStep('timetable');

        return $this->nextStep('examCalendar');
    }

    public function processTimetablePeriod($text)
    {
        $timetables = CalendarRepository::getTimetables($this->getConversation()->notes['degree']);
        if(count($timetables) === 1)
        {
            // Skip this step since there is only one option
            $this->getConversation()->notes['period'] = array_keys($timetables)[0];
            return $this->nextStep();
        }

        $opts = array_keys($timetables);
        $keyboard = array_chunk($opts, 1);
        $keyboard[] = [self::CANCEL];

        $this->getRequest()->keyboard($keyboard);

        if ($this->isProcessed() || empty($text)) {
            return $this->getRequest()->sendMessage("Selecciona el periodo");
        }
        if ($text === self::CANCEL) {
            return $this->cancelConversation();
        }
        if (!(in_array($text, $opts))) {
            return $this->getRequest()->sendMessage("Selecciona una opción del teclado por favor");
        }

        $this->getConversation()->notes['period'] = $text;
        return $this->nextStep();
    }

    public function processTimetableSemester($text)
    {
        $timetables = CalendarRepository::getTimetables($this->getConversation()->notes['degree'])[$this->getConversation()->notes['period']];
        $opts = array_keys($timetables);
        $keyboard = array_chunk($opts, 2);
        $keyboard[] = [self::CANCEL];

        $this->getRequest()->keyboard($keyboard);

        if ($this->isProcessed() || empty($text)) {
            return $this->getRequest()->sendMessage("Selecciona el semestre");
        }
        if ($text === self::CANCEL) {
            return $this->cancelConversation();
        }
        if (!(in_array($text, $opts))) {
            return $this->getRequest()->sendMessage("Selecciona una opción del teclado por favor");
        }

        $timetable = $timetables[$text];
        $result = $this->getRequest()->hideKeyboard()->caption($timetable->caption)->sendDocument($timetable->link);
        $this->stopConversation();

        return $result;
    }

    public function processExamCalendar($text)
    {
        $examCalendars = CalendarRepository::getExamCalendars($this->getConversation()->notes['degree']);
        $opts = array_keys($examCalendars);
        $keyboard = array_chunk($opts, 1);
        $keyboard[] = [self::CANCEL];

        $this->getRequest()->keyboard($keyboard);

        if ($this->isProcessed() || empty($text)) {
            return $this->getRequest()->sendMessage("Selecciona el período de exámenes");
        }
        if ($text === self::CANCEL) {
            return $this->cancelConversation();
        }
        if (!(in_array($text, $opts))) {
            return $this->getRequest()->sendMessage("Selecciona una opción del teclado por favor");
        }

        $examCalendar = $examCalendars[$text];
        $result = $this->getRequest()->hideKeyboard()->caption($examCalendar->caption)->sendDocument($examCalendar->link);
        $this->stopConversation();

        return $result;
    }
    
    public function processBusinessCalendar($text)
    {
        $businessCalendars = CalendarRepository::getBusinessCalendars();
        $opts = array_keys($businessCalendars);
        $keyboard = array_chunk($opts, 1);
        $keyboard[] = [self::CANCEL];
        
        $this->getRequest()->keyboard($keyboard);
        
        if($this->isProcessed() || empty($text))
        {
            return $this->getRequest()->sendMessage("Selecciona el plan de estudios");
        }
        if($text === self::CANCEL)
        {
            return $this->cancelConversation();
        }
        if( !(in_array($text, $opts)) )
        {
            return $this->getRequest()->sendMessage("Selecciona una opción del teclado por favor");
        }

        $businessCalendar = $businessCalendars[$text];
        $result = $this->getRequest()->hideKeyboard()->caption($businessCalendar->caption)->sendDocument($businessCalendar->link);
        $this->stopConversation();

        return $result;
    }
    
}