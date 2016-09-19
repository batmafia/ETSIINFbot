<?php
/**
 * This file is part of the TelegramBot package.
 *
 * (c) Avtandil Kikabidze aka LONGMAN <akalongman@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Commands\User;

use Commands\Base\BaseUserCommand;

/**
 * User "/cafeteria" command
 */
class CafeteriaCommand extends BaseUserCommand
{
    /**#@+
     * {@inheritdoc}
     */
    public $enabled = true;

    protected $name = 'cafeteria';
    protected $description = 'Consulta el menú de la cafetería de la ETSIINF.';
    protected $usage = '/cafeteria';
    protected $version = '0.1.0';
    protected $need_mysql = true;
    /**#@-*/


    /**
     * Global Vars
     */



    /**
     * [process_SelectMenu description]
     * @param  [type] $text [description]
     * @return [type]       [description]
     */
    public function process_SelectMenu($text)
    {

        $opts = ['Lun','Mar','Mie','Jue','Vie'];
        $opts2= ['Menú Semanal Completo'];
        $cancel = ['Cancelar'];
        $keyboard = [$opts,$opts2,$cancel];
        $titleKeyboard = 'Selecciona si deseas consultar un dia o el menú completo';
        $msgErrorImputKeyboard = 'Selecciona una opción del teclado por favor:';

        $this->getConversation();

        $this->getRequest()->keyboard($keyboard);

        if ( $this->isProcessed() || empty($text) )
        {
            return $this->getRequest()->sendMessage($titleKeyboard);
        }

        if( !(in_array($text, $opts) || in_array($text, $cancel)) || in_array($text, $opts2))
        {
            return $this->getRequest()->sendMessage($msgErrorImputKeyboard);
        }

        if (in_array($text, $cancel))
        {
            return $this->cancelConversation();
        }

        $this->getConversation()->notes['option'] = $text;
        $this->stopConversation();
        return 0;
    }

    /**
     * [cancelConversation description]
     * @return [type] [description]
     */
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
