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
use app\models\repositories\BusRepository;
use app\commands\base\BaseUserCommand;

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
    const ETSIINF = 'ETSIINF';
    const MADRID = 'Madrid';



    /**
     * [process_SelectLine description]
     * @param  [type] $text [description]
     * @return [type]       [description]
     */
    public function processSelectLine($text)
    {

        $opts = ['591','865','571','573'];
        $cancel = ['Cancelar'];
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

        $opts = [self::ETSIINF,self::MADRID];
        $cancel = ['Cancelar'];
        $keyboard = [$opts,$cancel];
        $titleKeyboard = 'Selecciona donde te encuentras actualmente:';
        $msgErrorImputKeyboard = 'Selecciona una opción del teclado por favor:';


        $this->getRequest()->keyboard($keyboard);

        if ($this->isProcessed() || empty($text))
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

        $this->getConversation()->notes['location'] = $text;
        return $this->nextStep();
    }


    /**
     * [process_SendLineInfo description]
     * @return [type]       [description]
     */
    public function processSendLineInfo()
    {

        $lineId = $this->getConversation()->notes['line'];
        $location = $this->getConversation()->notes['location'];
        $stopId = $this->getStopId($lineId, $location);
        $stop = BusRepository::getBusStop($stopId);
        $busIcon = "\xF0\x9F\x9A\x8C"; // http://apps.timwhitlock.info/unicode/inspect/hex/1F68C
        #$stopIcon = "\xF0\x9F\x9A\x8F"; // http://apps.timwhitlock.info/unicode/inspect/hex/1F68F


        if (empty($stop->getLinesByNumber($lineId))) {
            $outText = "$busIcon *No hay próximas llegadas* para el bus *$lineId* a la parada *$stop->stopName* \n";
        }
        else
        {
            $outText = "$busIcon El bus *$lineId* saldrá de la parada *$stop->stopName*:\n";

            foreach($stop->getLinesByNumber($lineId) as $line)
            {
                $waitTimeMinutes = $line->getWaitMinutes();
                $msg = "";
                switch (true)
                {
                    case ($waitTimeMinutes == 0):
                        # TODO: mirar si es una parada intermedia (Llegando a la parada )o no (Saliendo de la parada)
                        $msg .= "*En la parada*";
                        break;
                    case ($waitTimeMinutes  <= 60):
                        $msg .= "En *$waitTimeMinutes minuto";
                        if($waitTimeMinutes > 1)
                            $msg .= "s";
                        $msg .= "*";
                        break;
                    case ($waitTimeMinutes > 60):
                        $hours = floor($waitTimeMinutes/60);
                        $mins = $waitTimeMinutes%60;
                        $msg .= "A las *$hours:$mins*";
                        break;
                    default:
                        $msg .= "$waitTimeMinutes NO VALIDO";
                        break;
                }
                $outText .= " - ".$msg.".\n";
            }
        }

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
                '865' => '8-1684',
                '571' => '15782',
                '573' => '11278'
            ]
        ][$location][$busLine];
    }

}
