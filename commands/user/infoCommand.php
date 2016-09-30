<?php

namespace app\commands\user;
use app\commands\base\BaseUserCommand;

/**
 * User "/bus" command
 */
class InfoCommand extends BaseUserCommand
{
    /**#@+
     * {@inheritdoc}
     */
    public $enabled = true;

    protected $name = 'info';
    protected $description = 'Consulta información rápida sobre algunos temas de la facultad.';
    protected $usage = '/info';
    protected $version = '0.1.0';
    protected $need_mysql = true;
    /**#@-*/


    private $options;

    /**
     * [process_SelectLine description]
     * @param  [type] $text [description]
     * @return [type]       [description]
     */
    public function processOptions($text)
    {

        $opts = ['Asociaciones','Secretaria','WIFI','VPN','FTP'];
        $cancel = ['Cancelar'];
        $keyboard = [$opts,$cancel];
        $titleKeyboard = 'Selecciona una opción';
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

        $this->options['opt'] = $text;
        $outText = "";
        // para llamar a alguna en cada caso
        switch ($text) {
            case $opts[0]:
                $outText = $this->processAssciaciones();
                break;
            default:
                break;
        }
        $result = $this->getRequest()->hideKeyboard()->markdown()->sendMessage($outText);
        $this->stopConversation();
        return $result;
    }



    private function processAssciaciones()
    {
        $outText = "PRUEBA";
        return $outText;
    }


    /**
     * [cancelConversation description]
     * @return [type] [description]
     */
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
