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
    const POZUELO = 'Pozuelo';

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
        $opts = $this->getOrigin();

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


        $dest = $this->getDestination($this->getConversation()->notes['origin']);

        if (sizeof($dest) === 1) {
            $this->getConversation()->notes['destination'] = $dest[0];
            return $this->nextStep('sendLineInfo');
        } else {
            return $this->nextStep();
        }
    }



    /**
     * [process_SelectLocation description]
     * @param  [type] $text [description]
     * @return [type]       [description]
     */
    public function processSelectDestination($text)
    {
        $opts = $this->getDestination($this->getConversation()->notes['origin']);
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
            $stopId_tmp = $this->getStopId($lineID, $origin, $destination);
            $stopsIDs[$stopId_tmp][] = $lineID;
        }

        $lineswithoutExits = [];
        foreach ($stopsIDs as $stopID => $linesIDsByStop) {

            try {
                $stop = BusRepository::getBusStop($stopID);
                $stop_array[$stopID] = $stop;
            } catch (\Exception $exception) {
                $result = $this->getRequest()->hideKeyboard()->markdown()->sendMessage("Parece que la API del Consorcio de Transportes " .
                    "de Madrid no está disponible en estos momentos y por ello *no te podemos mostrar las próximas " .
                    "llegadas.*\n Prueba a realizar la consulta más tarde.\n\n");
                $this->stopConversation();
                return $result;
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
//        http://www.crtm.es/widgets/#/line/8__571___/route/8__571____2_-_IT_1
//        http://www.crtm.es/widgets/api/GetLineLocation.php?mode=8&codItinerary=8__571____2_-_IT_1&codLine=8__571___&codStop=8_08875&direction=
//        $longitude = "-3.8778300285339";
//        $latitude = "40.404960632324";
//        $result = $this->getRequest()->hideKeyboard()->sendLocation($longitude, $latitude);
        $this->stopConversation();

        return $result;
    }


    /**
     * @return array
     */
    private function getOrigin()
    {
        return [
            self::ETSIINF,
            self::ALUCHE,
            self::COLONIA,
            self::MONCLOA,
            self::BOADILLA,
            self::POZUELO
        ];
    }

    /**
     * @param $locationOrigin
     * @return mixed
     */
    private function getDestination($locationOrigin)
    {
        return [
            self::ETSIINF => [
                self::ALUCHE,
                self::COLONIA,
                self::MONCLOA,
                self::BOADILLA,
                self::POZUELO
            ],
            self::ALUCHE => [
                self::ETSIINF
            ],
            self::COLONIA => [
                self::ETSIINF
            ],
            self::MONCLOA => [
                self::ETSIINF
            ],
            self::BOADILLA => [
                self::ETSIINF
            ],
            self::POZUELO => [
                self::ETSIINF
            ]
        ][$locationOrigin];
    }


    /**
     * @param $busLine
     * @param $origin
     * @param $destination
     * @return mixed
     */
    private function getStopId($busLine, $origin, $destination)
    {
        return [
            self::ETSIINF => [
                '591' => [
                    self::COLONIA => '08411',
                    self::ALUCHE => '08411'
                ],
                '865' => [
                    self::MONCLOA => '17573'
                ],
                '571' => [
                    self::COLONIA => '08771',
                    self::ALUCHE => '08771',
                    self::BOADILLA => '08758'
                ],
                '573' => [
                    self::COLONIA => '08771',
                    self::MONCLOA => '08771',
                    self::BOADILLA => '08758'
                ],
                '566' => [
                    self::POZUELO => '08758',
                    self::BOADILLA => '08771'
                ],
                'N905' => [
                    self::COLONIA => '08771',
                    self::MONCLOA => '08771',
                    self::BOADILLA => '08758'
                ]
            ],
            self::ALUCHE => [
                '591' => [
                    self::ETSIINF => '08380'
                ],
                '571' => [
                    self::ETSIINF => '15782'
                ]
            ],
            self::COLONIA => [
                '591' => [
                    self::ETSIINF => '08409',
//                    self::ALUCHE => '08410'
                ],
                '571' => [
                    self::ETSIINF => '08409',
//                    self::ALUCHE => '08410'
                ],
                '573' => [
                    self::ETSIINF => '08409',
//                    self::ALUCHE => '08410'
                ],
                'N905' => [
                    self::ETSIINF => '08409',
                ],
            ],
            self::MONCLOA => [
                '865' => [
                    self::ETSIINF => '11278'
                ],
                '573' => [
                    self::ETSIINF => '11278'
                ],
                'N905' => [
                    self::ETSIINF => '11278'
                ]
            ],
            self::BOADILLA => [
                '571' => [
                    self::ETSIINF => '08875'
                ],
                '573' => [
                    self::ETSIINF => '15580'
                ],
                '566' => [
                    self::ETSIINF => '17902'
                ],
                'N905' => [
                    self::ETSIINF => '15579'
                ]
            ],
            self::POZUELO => [
                '566' => [
                    self::ETSIINF => '09047'
                ]
            ]
        ][$origin][$busLine][$destination];
    }

    /**
     * @param $location
     * @return mixed
     */
    private function getLines($location)
    {
        return [
            self::ETSIINF => [ '591', '865', '571', '573', '566', 'N905' ],
            self::ALUCHE => [ '591', '571' ],
            self::COLONIA => [ '591', '571', '573', 'N905' ],
            self::MONCLOA => [ '865', '573', 'N905' ],
            self::BOADILLA => [ '571', '573', '566', 'N905' ],
            self::POZUELO => [ '566' ]
        ][$location];

    }

    /**
     * @param $origin
     * @return string
     */
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
