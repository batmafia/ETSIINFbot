<?php
/**
 * This file is part of the TelegramBot package.
 *
 * (c) Avtandil Kikabidze aka LONGMAN <akalongman@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace app\commands\user;
use app\commands\base\Request;
use app\models\repositories\BusRepository;
use app\commands\base\BaseUserCommand;
use \DateTime;
use \DateTimeZone;


/**
 * User "/bus" command
 */
class BusCommand extends BaseUserCommand
{
    /**#@+
     * {@inheritdoc}
     */
    public $enabled = true;

    protected $name = 'bus';
    protected $description = 'Consulta el tiempo que queda para que salga el autobús.';
    protected $usage = '/bus';
    protected $version = '0.1.0';
    protected $need_mysql = true;
    /**#@-*/


    /**
     * Global Vars
     */
    const CANCELAR = 'Cancelar';
    const ATRAS = 'Atrás';
    const NUEVA_BUSQUEDA = 'Nueva Búsqueda';

    const ETSIINF = 'ETSIINF';
    const MADRID = 'Madrid';
    const TODAS = 'Todas';
    const ACTUALES = 'Actuales';
    const ORIGINALES = 'Carteles oficiales';


    /**
     * [process_SelectLine description]
     * @param  [type] $text [description]
     * @return [type]       [description]
     */
    public function processSelectLine($text)
    {

        $opts = ['591','865','571','573'];
        $cancel = [self::CANCELAR];
        $keyboard = [$opts,$cancel];
        $titleKeyboard = 'Selecciona una línea';
        $msgErrorImputKeyboard = 'Selecciona una opción del teclado por favor:';

        $this->getConversation();

        $this->getRequest()->keyboard($keyboard);

        if ( $this->isProcessed() || empty($text) )
        {
            return $this->getRequest()->sendMessage($titleKeyboard);
        }

        if( !(in_array($text, $opts) || in_array($text, $cancel)) )
        {
            return $this->getRequest()->sendMessage($msgErrorImputKeyboard);
        }

        if (in_array($text, $cancel))
        {
            return $this->cancelConversation();
        }

        $this->getConversation()->notes['line'] = $text;
        return $this->nextStep();
    }


    /**
     * [process_SelectLocation description]
     * @param  [type] $text [description]
     * @return [type]       [description]
     */
    public function processSelectLocation($text)
    {

        $opts = [self::ETSIINF, self::MADRID];
        $keyboard = [$opts];
        $keyboard [] = [self::CANCELAR,self::ATRAS];
        $titleKeyboard = 'Selecciona donde te encuentras actualmente:';
        $msgErrorImputKeyboard = 'Selecciona una opción del teclado por favor:';


        $this->getRequest()->keyboard($keyboard);


        if ($this->isProcessed() || empty($text))
        {
            return $this->getRequest()->sendMessage($titleKeyboard);
        }

        if ($text === self::ATRAS)
        {
            return $this->previousStep();
        }

        if ($text === self::CANCELAR)
        {
            return $this->cancelConversation();
        }

        if( ! in_array($text, $opts))
        {
            return $this->getRequest()->sendMessage($msgErrorImputKeyboard);
        }

        $this->getConversation()->notes['location'] = $text;
        return $this->nextStep();
    }


    /**
     * @param $text
     * @return \Longman\TelegramBot\Entities\ServerResponse
     */
    public function processSelectScheduleType($text)
    {
        $opts = [self::ACTUALES, self::TODAS, self::ORIGINALES];
        $keyboard = array_chunk(($opts), 2);
        $keyboard [] = [self::CANCELAR, self::ATRAS];
        $titleKeyboard = '¿Quieres ver las salidas actuales o todas las salidas del día?';
        $msgErrorImputKeyboard = 'Selecciona una opción del teclado por favor:';


        $this->getRequest()->keyboard($keyboard);


        if ($this->isProcessed() || empty($text))
        {
            return $this->getRequest()->sendMessage($titleKeyboard);
        }

        if ($text === self::ATRAS)
        {
            return $this->previousStep();
        }

        if ($text === self::CANCELAR)
        {
            return $this->cancelConversation();
        }

        if( ! in_array($text, $opts))
        {
            return $this->getRequest()->sendMessage($msgErrorImputKeyboard);
        }


        $this->getConversation()->notes['scheduleType'] = $text;
        if ($text === self::ACTUALES)
        {
            return $this->nextStep();
        }
        elseif ($text === self::TODAS)
        {
            return $this->processSendFullTimeBuses();
        }
        elseif ($text === self::ORIGINALES)
        {
            return $this->processSendOriginal();
        }
        else
        {
            return $this->getRequest()->sendMessage($msgErrorImputKeyboard);
        }

    }


    /**
     * [process_SendLineInfo description]
     * @return [type]       [description]
     */
    public function processSendLineInfo()
    {

        $this->getRequest()->sendAction(Request::ACTION_TYPING);

        $lineId = $this->getConversation()->notes['line'];
        $location = $this->getConversation()->notes['location'];
        $stopId = $this->getStopId($lineId, $location);
        $busIcon = "\xF0\x9F\x9A\x8C"; // http://apps.timwhitlock.info/unicode/inspect/hex/1F68C

        try
        {
            $stop = BusRepository::getBusStop($stopId);
        }
        catch (\Exception $exception)
        {
            if ($exception->getMessage() == "Unable to parse response as JSON"
                || preg_match('/Unable to connect to /',$exception->getMessage()))
            {
                $this->getRequest()->markdown()->sendMessage("Parece que la API del Consorcio de Transportes ".
                    "de Madrid no está disponible en estos momentos y por ello *no te podemos mostrar las próximas ".
                    "llegadas.*\n Prueba a realizar la consulta más tarde.\n\n");

                return $this->processSendFullTimeBuses();

            }
            else
            {
                throw $exception;
            }
        }


        $lineByNumber = $stop->getLinesByNumber($lineId);
        if (empty($lineByNumber))
        {
            $outText = "$busIcon *No hay próximas llegadas* para el bus *$lineId* a la parada *$stop->stopName* \n";
            $this->getRequest()->hideKeyboard()->markdown()->sendMessage($outText);
            return $this->nextStep();
        }
        else
        {
            $outText = "$busIcon El bus *$lineId* saldrá de la parada *$stop->stopName*:\n";

            foreach($lineByNumber as $line)
            {
                $msg = $line->getWaitHumanTime();
                $outText .= " - $msg\n";
            }
        }


        $result = $this->getRequest()->hideKeyboard()->markdown()->sendMessage($outText);
        $this->stopConversation();
        return $result;
    }



    /**
     * @return \Longman\TelegramBot\Entities\ServerResponse
     * @throws \Exception
     */
    public function processSendFullTimeBuses()
    {
        $this->getRequest()->sendAction(Request::ACTION_TYPING);

        $lineId = $this->getConversation()->notes['line'];
        $location = $this->getConversation()->notes['location'];
        $scheduleType = $this->getConversation()->notes['scheduleType'];
        $busIcon = "\xF0\x9F\x9A\x8C"; // http://apps.timwhitlock.info/unicode/inspect/hex/1F68C

        try
        {
            $fullTimeBuses = BusRepository::getFullTimeBusesOpts($lineId, $location, false);
        }
        catch (\Exception $exception)
        {
            if ($exception->getMessage() == "Unable to parse response as JSON"
                || preg_match('/Unable to connect to /',$exception->getMessage()))
            {
                $result = $this->getRequest()->hideKeyboard()->markdown()->sendMessage("Parece que no podemos ver todas las salidas.\n Prueba a realizar la consulta más tarde.\n\n");
                $this->stopConversation();
                return $result;
            }
            else
            {
                throw $exception;
            }
        }


        $lastEl = end($fullTimeBuses);
        $lastTimeBusSTR = "$lastEl:00";
        $lastTimeBus = strtotime($lastTimeBusSTR);
        $nowTimeSTR = $this->myDateFormat("H:i:s", false); // false for timeestamp
        $nowTime = strtotime($nowTimeSTR);

        $outText = "";

        // echo "lastTimeBus = $lastTimeBus -> $lastTimeBusSTR\n";
        // echo "nowTime = $nowTime -> $nowTimeSTR \n";
        // $v = $nowTime < $lastTimeBus;
        // echo "nowTime < lastTimeBus = $v \n";
        // echo "scheduleType = $scheduleType\n";
        // $v = $scheduleType==='Todas';
        // echo "scheduleType==='Todas' = $v \n";
        // $v = $lastTimeBus > $nowTime || $scheduleType === 'Todas';
        // echo "TODOIF = $v \n";

        if( $scheduleType === 'Todas')
        {
            if ($nowTime <= $lastTimeBus)
            {
                // Send all buses today
                $outText .= "$busIcon El bus *$lineId* tiene todas las siguientes salidas desde *$location* para hoy:\n\n";
            }
            else
            {
                // Send all the next day buses
                $outText .= "$busIcon El bus *$lineId* no tiene mas salidas desde *$location* para hoy.\n";
                $outText .= "Las salidas para ";
                $getNextAvailableBusesTime = $this->getNextAvailableBusesTime($lineId, $location);
                $outText .= $getNextAvailableBusesTime[0];
                $nowTime = $getNextAvailableBusesTime[1];
                $fullTimeBuses = $getNextAvailableBusesTime[2];
            }
            $outText .= implode(", ", $fullTimeBuses);

        }
        // This case for API crash
        elseif ($scheduleType === 'Actuales')
        {
            if ($nowTime <= $lastTimeBus)
            {
                // Send the next buses
                $outText .= "$busIcon El bus *$lineId* tiene las siguientes próximas salidas desde *$location*:\n\n";
            }
            else
            {
                // Send the next day buses
                $outText .= "$busIcon El bus *$lineId* no tiene mas salidas desde *$location* para hoy.\n";
                $outText .= "Las primeras salidas para ";
                $getNextAvailableBusesTime = $this->getNextAvailableBusesTime($lineId, $location);
                $outText .= $getNextAvailableBusesTime[0];
                $nowTime = $getNextAvailableBusesTime[1];
                $fullTimeBuses = $getNextAvailableBusesTime[2];
            }
            $nextTimeBuses = array();
            foreach ($fullTimeBuses as $key => $time)
            {
                $nextBusTimeStopSTR = "$time:00";
                $nextBusTimeStop = strtotime($nextBusTimeStopSTR);
                if ($nowTime <= $nextBusTimeStop)
                {
                    $nextTimeBuses[] = $time;
                }

                if(sizeof($nextTimeBuses)==3) break;
            }
            $outText .= implode(", ", $nextTimeBuses);
        }
        else
        {
            // In other case
            $result = \Longman\TelegramBot\Request::emptyResponse();
            return $result;
        }

        $result = $this->getRequest()->hideKeyboard()->markdown()->sendMessage($outText);
        $this->stopConversation();
        return $result;
    }



    /**
     * @return \Longman\TelegramBot\Entities\ServerResponse
     */
    public function processSendOriginal()
    {

        $this->getRequest()->sendAction(Request::ACTION_TYPING);

        $lineId = $this->getConversation()->notes['line'];
        $location = $this->getConversation()->notes['location'];
        $scheduleType = $this->getConversation()->notes['scheduleType'];

        $outText = "Aquí tienes los carteles oficiales de la línea *" . $lineId . "*:\n";
        $result = $this->getRequest()->hideKeyboard()->markdown()->sendMessage($outText);

        $urlH1 = "http://www.crtm.es/datos\_lineas/horarios/8" . $lineId . "H1" . ".pdf";
        $outText = "*Ida*: " . $urlH1 . "\n";
        $result = $this->getRequest()->hideKeyboard()->markdown()->sendMessage($outText);

        $urlH2 = "http://www.crtm.es/datos\_lineas/horarios/8" . $lineId . "H2" . ".pdf";
        $outText = "*Vuelta*: " . $urlH2 . "\n";
        $result = $this->getRequest()->hideKeyboard()->markdown()->sendMessage($outText);

        $this->stopConversation();
        return $result;
    }


    /**
     * [getStopId description]
     * @param  [type] $busline  [description]
     * @param  [type] $location [description]
     * @return [type]           [description]
     */
    private function getStopId($busLine, $location)
    {
        return [
            self::ETSIINF => [
                '591' => '08411',
                '865' => '17573',
                '571' => '08771',
                '573' => '08771'
            ],
            self::MADRID => [
                '591' => '08380',
                '865' => '11278',
                '571' => '15782',
                '573' => '11278'
            ]
        ][$location][$busLine];
    }


    /**
     * http://php.net/manual/es/function.date.php
     * @param  string  $format    [description]
     * @param  boolean $timestamp [description]
     * @param  boolean $timezone  [description]
     * @return [type]             [description]
     */
    function myDateFormat($format="r", $timestamp=false, $timezone=false)
    {
        $userTimezone = new DateTimeZone(!empty($timezone) ? $timezone : 'GMT');
        $gmtTimezone = new DateTimeZone('GMT');
        $myDateTime = new DateTime(($timestamp!=false?date("r",(int)$timestamp):date("r")), $gmtTimezone);
        $offset = $userTimezone->getOffset($myDateTime);
        return date($format, ($timestamp!=false?(int)$timestamp:$myDateTime->format('U')) + $offset);
    }



    function getNextAvailableBusesTime($lineId, $location)
    {
        $nDays = 0;
        $outText = "";
        do {
            $nDays++;
            $nextDayTimestamp = strtotime("+$nDays day");
            try
            {
                $fullTimeBuses = BusRepository::getFullTimeBusesOpts($lineId, $location, $nextDayTimestamp);
            }
            catch (\Exception $exception)
            {
                if ($exception->getMessage() == "Unable to parse response as JSON"
                    || preg_match('/Unable to connect to /',$exception->getMessage()))
                {
                    $result = $this->getRequest()->hideKeyboard()->markdown()->sendMessage("Parece que no podemos ver todas las salidas.\n Prueba a realizar la consulta más tarde.\n\n");
                    $this->stopConversation();
                    return $result;
                }
                else
                {
                    throw $exception;
                }
            }
        } while (sizeof($fullTimeBuses)==0);

        $nowTime = $this->myDateFormat("H:i:s", $nextDayTimestamp, 'Europe/Madrid');

        if ($nDays == 1)
        {
            $outText .= "mañana:\n";
        }
        else
        {
            $nextDayAvailableBuses = $this->myDateFormat("j", $nextDayTimestamp, 'Europe/Madrid');
            $nextMonthAvailableBuses = $this->myDateFormat("m", $nextDayTimestamp, 'Europe/Madrid');
            $nextYearAvailableBuses = $this->myDateFormat("Y", $nextDayTimestamp, 'Europe/Madrid');
            $outText .= "$nextDayAvailableBuses/$nextMonthAvailableBuses/$nextYearAvailableBuses:\n";
        }
        $ret = array();
        $ret[] = $outText;
        $ret[] = $nowTime;
        $ret[] = $fullTimeBuses;
        return $ret;
    }

}
