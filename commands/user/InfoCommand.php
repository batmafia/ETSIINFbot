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

    const KEYBOARD_COLUMNS = 3;

    /**
     * [process_SelectLine description]
     * @param  [type] $text [description]
     * @return [type]       [description]
     */
    public function processOptions($text)
    {
        //TODO: Use actual $infoArray from repository
        $infoArray = [
            'VPN' => 'Info de VPN',
            'Asociaciones' => [
                'ACM'=>'Info de ACM',
                'ASCFI'=>'Info de ASCFI'
            ],
            'Horarios'=>'Info de horarios',
            'Otros'=>[
                'Yo que se'=>'Eso'
            ]
        ];

        $opts = $this->getCurrentOptions($infoArray);

        if(!is_array($opts))
        {
            //We reached last level, send the info.
            $this->stopConversation();
            return $result = $this->getRequest()->hideKeyboard()->markdown()->sendMessage($opts);
        }
        else
        {
            $opts = array_keys($opts);
            $cancel = ['Cancelar'];
            $keyboard = array_chunk($opts, self::KEYBOARD_COLUMNS);
            $keyboard[] = $cancel;

            $this->getRequest()->keyboard($keyboard);

            if (empty($text)) {
                return $this->getRequest()->sendMessage('Selecciona una opción');
            }

            if (!(in_array($text, $opts) || in_array($text, $cancel))) {
                return $this->getRequest()->sendMessage('Selecciona una opción del teclado por favor:');
            }

            if (in_array($text, $cancel)) {
                return $this->cancelConversation();
            }

            $this->getConversation()->notes['indexes'][] = $text;
            return $this->processOptions(null);
        }
    }

    private function getCurrentOptions($infoArray)
    {
        if(!isset($this->getConversation()->notes['indexes']))
            $this->getConversation()->notes['indexes'] = [];

        $opts = $infoArray;
        foreach ($this->getConversation()->notes['indexes'] as $index)
        {
            $opts = $opts[$index];
        }

        return $opts;
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
