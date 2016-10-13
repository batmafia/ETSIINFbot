<?php
/**
 * Created by PhpStorm.
 * User: Diego
 * Date: 26/9/16
 * Time: 19:26
 */

namespace app\commands\user;
use app\models\repositories\MetroligeroRepository;
use app\commands\base\BaseUserCommand;
use app\commands\base\Request;

/**
 * User "/metroligero" command
 */
class MetroligeroCommand extends BaseUserCommand
{
    /**
     * {@inheritdoc}
     */
    public $enabled = true;
    protected $name = 'metroligero';
    protected $description = 'Consulta los minutos que quedan para que salga el metroligero.';
    protected $usage = '/metroligero';
    protected $version = '0.1.0';
    protected $need_mysql = true;


    const COLONIA_JARDIN = 'Colonia Jardin';
    const MONTEPRINCIPE = 'Montepríncipe';
    const PUERTA_BOADILLA = 'Puerta Boadilla';

    private $stops = [
        self::COLONIA_JARDIN => '201',
        self::MONTEPRINCIPE => '353',
        self::PUERTA_BOADILLA => '362'
    ];

    public function processLocation($text)
    {
        $this->getConversation();

        $cancel = ['Cancelar'];
        $keyboard = [array_keys($this->stops), $cancel];

        $this->getRequest()->keyboard($keyboard);
        if ( $this->isProcessed() || empty($text) )
        {
            return $this->getRequest()->sendMessage('¿Dónde te encuentras?');
        }
        if( !(in_array($text, array_keys($this->stops)) || in_array($text, $cancel)) )
        {
            return $this->getRequest()->sendMessage('Selecciona una opción del teclado por favor:');
        }
        if (in_array($text, $cancel))
        {
            return $this->cancelConversation();
        }
        $this->getConversation()->notes['location'] = $text;
        return $this->nextStep();
    }


    public function processLocation2($text)
    {
        $opts = [self::COLONIA_JARDIN, self::PUERTA_BOADILLA];
        $cancel = ['Cancelar'];
        $keyboard = [$opts, $cancel];

        $this->getRequest()->keyboard($keyboard);
        if ($this->isProcessed() || empty($text))
        {
            if($this->getConversation()->notes['location']=="Montepríncipe")
            {
                return $this->getRequest()->sendMessage('¿Hacia dónde te diriges?');
            }
            else
            {
                return $this->nextStep();
            }

        }
        if( !(in_array($text, $opts) || in_array($text, $cancel)) )
        {
            return $this->getRequest()->sendMessage('Selecciona una opción del teclado por favor:');
        }
        if (in_array($text, $cancel))
        {
            return $this->cancelConversation();
        }
        $this->getConversation()->notes['location2'] = $text;
        return $this->nextStep();
    }


    public function processSendLineInfo()
    {
        $this->getRequest()->sendAction(Request::ACTION_TYPING);

        $location1 = $this->getConversation()->notes['location'];
        if(empty($this->getConversation()->notes['location2']))
        {
            $llegadas = MetroligeroRepository::getMetroligeroStop($this->stops[$location1], $this->stops[self::MONTEPRINCIPE]);
        }
        else
        {
            $location2 = $this->getConversation()->notes['location2'];
            $llegadas = MetroligeroRepository::getMetroligeroStop($this->stops[$location1], $this->stops[$location2]);
        }

        $metroIcon = "\xF0\x9F\x9A\x89"; // http://apps.timwhitlock.info/unicode/inspect/hex/1F68C

        $arrivals = $llegadas->getArrivals();

        if ($arrivals[0] == 0 && $arrivals[1] == 0)
        {
            $outText = "$metroIcon *No hay más llegadas previstas para hoy.*";
        }
        else if ($arrivals[0] == 0 && $arrivals[1] != 0)
        {
            $outText = "$metroIcon El primer tren *está entrando en la estación*"
                . " y el siguiente llegará en *$arrivals[1] minutos*.";
        }
        else if($arrivals[0] != 0 && $arrivals[1] == 0)
        {
            $outText = "$metroIcon El último tren llegará en *$arrivals[1] minutos*.";
        }
        else if ($arrivals[0] != 0 && $arrivals[1] != 0)
        {
            $outText = "$metroIcon El primer tren llegará en *$arrivals[0] minutos*"
                . " y el siguiente en *$arrivals[1] minutos*.";
        }

        $result = $this->getRequest()->hideKeyboard()->markdown()->sendMessage($outText);
        $this->stopConversation();
        return $result;
    }

}
