<?php
/**
 * Created by PhpStorm.
 * User: Sergio
 * Date: 7/10/17
 * Time: 13:45
 */


namespace app\commands\user;
use app\commands\base\Request;
use app\models\repositories\BusRepository;
use app\commands\base\BaseUserCommand;
use app\models\repositories\MetroligeroRepository;
use \DateTime;
use \DateTimeZone;


/**
 * User "/bus" command
 */
class TransporteCommand extends BaseUserCommand
{
    /**#@+
     * {@inheritdoc}
     */
    public $enabled = true;

    protected $name = 'transporte';
    protected $description = 'Consulta el tiempo que queda para que salga el autobús.';
    protected $usage = '/transporte';
    protected $version = '0.0.1';
    protected $need_mysql = true;
    /**#@-*/


    /**
     * Global Vars
     */
    const CANCELAR = 'Cancelar';
    const ATRAS = 'Atrás';
    const NUEVA_BUSQUEDA = 'Nueva Búsqueda';

    const ETSIINF = 'ETSIINF';
    const ALUCHE = 'Aluche';
    const COLONIA = 'Colonia Jardín';
    const MONCLOA = 'Moncloa';
    const BOADILLA = 'Boadilla';

    const ML_COLONIA = '201';
    const ML_MONTEPRINCIPE = '353';
    const ML_PUERTABOADILLA = '362';


    const TODAS = 'Todas';
    const ACTUALES = 'Actuales';
    const ORIGINALES = 'Carteles oficiales';


    /**
     * [process_SelectLocation description]
     * @param  [type] $text [description]
     * @return [type]       [description]
     */
    public function processSelectOrigin($text)
    {
        $opts = [self::ETSIINF, self::ALUCHE, self::COLONIA, self::MONCLOA, self::BOADILLA];

        $keyboard = array_chunk(($opts), 2);
        $keyboard [] = [self::CANCELAR];
        $titleKeyboard = 'Selecciona donde te encuentras actualmente:';
        $msgErrorImputKeyboard = 'Selecciona una opción del teclado por favor:';


        $this->getConversation();

        $this->getRequest()->sendAction(Request::ACTION_TYPING);

        $this->getRequest()->keyboard($keyboard);


        if ($this->isProcessed() || empty($text))
        {
            return $this->getRequest()->sendMessage($titleKeyboard);
        }

        if ($text === self::CANCELAR)
        {
            return $this->cancelConversation();
        }

        if( ! in_array($text, $opts))
        {
            return $this->getRequest()->sendMessage($msgErrorImputKeyboard);
        }

        $this->getConversation()->notes['origin'] = $text;

        if ($text === self::ETSIINF) {

            return $this->nextStep();
        } else {
            $this->getConversation()->notes['destination'] = self::ETSIINF;
            return $this->nextStep('sendLineInfo');
        }
    }



    /**
     * [process_SelectLocation description]
     * @param  [type] $text [description]
     * @return [type]       [description]
     */
    public function processSelectDestination($text)
    {
        $opts = [self::ALUCHE, self::COLONIA, self::MONCLOA, self::BOADILLA];
        $keyboard = array_chunk(($opts), 2);
        $keyboard [] = [self::CANCELAR,self::ATRAS];
        $titleKeyboard = 'Selecciona donde quieres ir:';
        $msgErrorImputKeyboard = 'Selecciona una opción del teclado por favor:';


        $this->getRequest()->sendAction(Request::ACTION_TYPING);

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

        $this->getConversation()->notes['destination'] = $text;
        return $this->nextStep();
    }


    /**
     * [process_SendLineInfo description]
     * @return [type]       [description]
     */
    public function processSendLineInfo()
    {

        $this->getRequest()->sendAction(Request::ACTION_TYPING);

        $busIcon = "\xF0\x9F\x95\x92"; // https://apps.timwhitlock.info/emoji/tables/unicode


        $lineByNumber_array = [];
        $stop_array = [];
        $outText_lines = "";


        $origin = $this->getConversation()->notes['origin'];
        $linesIDs_ori = $this->getLines($origin);
        $destination = $this->getConversation()->notes['destination'];
        $linesIDs_des = $this->getLines($destination);
        $linesIDs = array_intersect($linesIDs_ori, $linesIDs_des);

        $stopsIDs = [];
        foreach ($linesIDs as $lineID) {
            $stopId_tmp = $this->getStopId($lineID, $origin);
            $stopsIDs[$stopId_tmp][] = $lineID;
        }

        $lineswithoutExits = [];
        foreach ($stopsIDs as $stopID => $linesIDsByStop) {

            try {
                $stop = BusRepository::getBusStop($stopID);
                $stop_array[$stopID] = $stop;
            } catch (\Exception $exception) {
                $emsg = $exception->getMessage();
                if ( $emsg == "Unable to parse response as JSON" || preg_match('/Unable to connect to /', $emsg ) ){
                    $result = $this->getRequest()->markdown()->sendMessage("Parece que la API del Consorcio de Transportes " .
                        "de Madrid no está disponible en estos momentos y por ello *no te podemos mostrar las próximas " .
                        "llegadas.*\n Prueba a realizar la consulta más tarde.\n\n");
                    $this->stopConversation();
                    return $result;

                } else {
                    throw $exception;
                }
            }


            foreach ($linesIDsByStop as $lineID) {
                $lineByNumber = $stop->getLinesByNumber($lineID);
                if (!empty($lineByNumber)) {
                    $lineByNumber_array[$lineID] = $lineByNumber;
                    $outText_lines .= " - *$lineID*: ";
                    foreach ($lineByNumber as $line) {
                        $msg = $line->getWaitHumanTime_transportes();
                        $outText_lines .= "$msg, ";
                    }
                    $outText_lines = substr($outText_lines, 0, -2);
                    $outText_lines .= ".\n";
                } else {
                    $lineswithoutExits[] = $lineID;
                }

            }

        }


        // metroligero
        $isMetro = false;
        if ($origin === self::COLONIA || $origin === self::BOADILLA) {
            $isMetro = true;
            $origin_ML_stopId = $this->getStopIdML($origin);
            $destination_ML_stopId = self::ML_MONTEPRINCIPE;
            $llegadas = MetroligeroRepository::getMetroligeroStop($origin_ML_stopId, $destination_ML_stopId);
        } elseif ( ($origin === self::ETSIINF) && ($destination === self::COLONIA || $destination === self::BOADILLA)) {
            $isMetro = true;
            $origin_ML_stopId = $this->getStopIdML($origin);
            $destination_ML_stopId = $this->getStopIdML($destination);
            $llegadas = MetroligeroRepository::getMetroligeroStop($origin_ML_stopId, $destination_ML_stopId);
        } else {
            $isMetro = false;
        }

        $outText_lines_ML = "";
        if ($isMetro) {

            $arrivals = $llegadas->getArrivals();

            $outText_lines_ML = " - *ML*: ";
            if ($arrivals[0] == 0 && $arrivals[1] == 0) {
                $lineswithoutExits[] = 'Metro ligero';
            } else if ($arrivals[0] == 0 && $arrivals[1] != 0) {
                $outText_lines_ML .= "Entrando, " . $arrivals[1] . " min.";
            } else if ($arrivals[0] != 0 && $arrivals[1] == 0) {
                $outText_lines_ML .= $arrivals[0] . " min.";
            } else if ($arrivals[0] != 0 && $arrivals[1] != 0) {
                $outText_lines_ML .= $arrivals[0] . " min, " . $arrivals[1] . " min.";
            }
            $outText_lines_ML .= "\n";
        }





        $outText_tosend = "";
        //print_r($lineByNumber_array);
        if (!empty($lineByNumber_array)){
            $outText_header = "$busIcon Próximas salidas de *$origin* con destino *$destination*:\n";
            $outText_tosend = $outText_header . $outText_lines . $outText_lines_ML;
            if (!empty($lineswithoutExits)){
                $outText_tosend = $outText_tosend . "No hay ninguna salida próxima de ninguno de los siguientes buses:\n*";
            }
        } else {
            $outText_tosend = "$busIcon No hay ninguna salida próxima de: *$origin* con destino: *$destination*, de ninguno de los siguientes buses:\n*";
        }

        if (!empty($lineswithoutExits)){
            $outText_lineswithoutExits = "- ";
            foreach ($lineswithoutExits as $lineID) {
                $outText_lineswithoutExits .= $lineID . ", ";
            }
            $outText_lineswithoutExits = substr($outText_lineswithoutExits, 0, -2);
            $outText_lineswithoutExits .= "*\n";

            $outText_tosend = $outText_tosend . $outText_lineswithoutExits;
        }

        $result = $this->getRequest()->hideKeyboard()->markdown()->sendMessage($outText_tosend);
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
            self::ALUCHE => [
                '591' => '08380',
                '571' => '15782',
            ],
            self::COLONIA => [
                '591' => '08409',
                '571' => '08409',
                '573' => '08409'
            ],
            self::MONCLOA => [
                '865' => '11278',
                '573' => '11278'
            ],
            self::BOADILLA => [
                '571' => '08875',
                '573' => '15580'
            ]
        ][$location][$busLine];
    }

    /**
     * [getStopId description]
     * @param  [type] $busline  [description]
     * @param  [type] $location [description]
     * @return [type]           [description]
     */
    private function getLines($location)
    {
        return [
            self::ETSIINF => [ '591', '865', '571', '573' ],
            self::ALUCHE => [ '591', '571' ],
            self::COLONIA => [ '591', '571', '573' ],
            self::MONCLOA => [ '865', '573' ],
            self::BOADILLA => [ '571', '573' ]
        ][$location];

    }

    private function getStopIdML($origin)
    {
        switch ($origin) {
            case self::COLONIA:
                return self::ML_COLONIA;
            case self::BOADILLA:
                return self::ML_PUERTABOADILLA;
            case self::ETSIINF:
                return self::ML_MONTEPRINCIPE;
        }
    }



}
