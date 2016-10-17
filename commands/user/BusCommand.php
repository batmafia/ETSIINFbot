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
    protected $version = '0.1.1';
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

        $this->getRequest()->sendAction(Request::ACTION_TYPING);

        $lineId = $this->getConversation()->notes['line'];
        $location = $this->getConversation()->notes['location'];
        $stopId = $this->getStopId($lineId, $location);
        $busIcon = "\xF0\x9F\x9A\x8C";
        $alertIcon = "\xE2\x9A\xA0";


        try
        {
            $stop = BusRepository::getBusStop($stopId);
        }
        catch (\Exception $exception)
        {
            if ($exception->getMessage() == "Unable to parse response as JSON")
            {
                $result = $this->getRequest()->markdown()->sendMessage("$alertIcon Parece que la API del Consorcio de Transportes ".
                "de Madrid no está disponible en estos momentos y por ello *no te podemos mostrar las próximas llegadas.*\n".
                "Prueba a realizar la consulta pasados unos minutos.\n");

                $this->stopConversation();
                return $result;
            }
            else
            {
                throw $exception;
            }
        }


        if (empty($stop->getLinesByNumber($lineId)))
        {
            $outText = "$busIcon *No hay próximas llegadas* para el bus *$lineId* a la parada *$stop->stopName* \n";
        }
        else
        {
            $outText = "$busIcon El bus *$lineId* saldrá de la parada *$stop->stopName*:\n";

            foreach($stop->getLinesByNumber($lineId) as $line)
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
