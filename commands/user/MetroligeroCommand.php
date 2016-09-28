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
/**
 * User "/metroligero" command
 */
class MetroligeroCommand extends BaseUserCommand
{
    /**#@+
     * {@inheritdoc}
     */
    public $enabled = true;
    protected $name = 'metroligero';
    protected $description = 'Consulta los minutos que quedan para que salga el metroligero.';
    protected $usage = '/metroligero';
    protected $version = '0.1.0';
    protected $need_mysql = true;
    /**#@-*/


    public function processLocation($text)
    {
        $opts = ['Colonia Jardín','Montepríncipe','Puerta Boadilla'];
        $cancel = ['Cancelar'];
        $keyboard = [$opts,$cancel];
        $titleKeyboard = '¿Dónde te encuentras?';
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
        $this->getConversation()->notes['location'] = $text;
        return $this->nextStep();
    }


    public function processLocation2($text)
    {
        $opts = ['Colonia Jardín','Puerta Boadilla'];
        $cancel = ['Cancelar'];
        $keyboard = [$opts,$cancel];
        $titleKeyboard = '¿Hacia dónde te diriges?';
        $msgErrorImputKeyboard = 'Selecciona una opción del teclado por favor:';
        $this->getRequest()->keyboard($keyboard);
        if ($this->isProcessed() || empty($text))
        {
            if($this->getConversation()->notes['location']=="Montepríncipe"){
                return $this->getRequest()->sendMessage($titleKeyboard);
            }else{
                return $this->nextStep();
            }

        }
        if( !(in_array($text, $opts) || in_array($text, $cancel)) )
        {
            return $this->getRequest()->sendMessage($msgErrorImputKeyboard);
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
        $location1=$this->getConversation()->notes['location'];

        if(empty($this->getConversation()->notes['location2']))
        {
            if ($location1=="Colonia Jardín"){
                $llegadas = MetroligeroRepository::getMetroligeroStop('201','353');
            }else{
                $llegadas = MetroligeroRepository::getMetroligeroStop('362','353');
            }


        }else{

            $location2=$this->getConversation()->notes['location2'];
            if($location2="Colonia Jardín"){
                $llegadas = MetroligeroRepository::getMetroligeroStop('353','201');
            }else{
                $llegadas = MetroligeroRepository::getMetroligeroStop('353','362');
            }

        }

        $metroIcon = "\xF0\x9F\x9A\x89"; // http://apps.timwhitlock.info/unicode/inspect/hex/1F68C

        $outText = "$metroIcon El primer tren llegará en *".$llegadas->getFirstStopMinutes()." min*".
            " y el siguiente en *".$llegadas->getSecondStopMinutes()." min*.";


        $result = $this->getRequest()->hideKeyboard()->markdown()->sendMessage($outText);
        $this->stopConversation();
        return $result;
    }


    private function cancelConversation()
    {
        $msgCancel = "Comando cancelado.";
        $msgThanks = "Gracias por usar ETSIINFbot.";
        $heart = "\xE2\x9D\xA4"; // http://apps.timwhitlock.info/unicode/inspect/hex/2764
        $sign = "ETSIINFbot by Batmafia with".$heart.".";
        $msgCancelConver = $msgCancel."\n".$msgThanks."\n".$sign;
        $result = $this->getRequest()->hideKeyboard()->sendMessage($msgCancelConver);
        $this->stopConversation();
        return $result;
    }
}