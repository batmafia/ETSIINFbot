<?php

namespace app\commands\user;

use app\commands\base\BaseUserCommand;
use Longman\TelegramBot\Request;

/**
 * User "/contacta" command
 */
class ContactaCommand extends BaseUserCommand
{
    /**#@+
     * {@inheritdoc}
     */
    protected $name = 'contacta';
    protected $description = 'Muestra los admin del bot para hablarles.';
    protected $usage = '/contacta';
    protected $version = '1.0.0';
    /**#@-*/

    const CANCEL = 'Cancelar';

    /**
     * [process_SelectLine description]
     * @param  [type] $text [description]
     * @return [type]       [description]
     */
    public function processSendAdmins($text)
    {

        $mensaje = "*Todas vuestras sugerencias o errores que observéis del bot escribidlas a *@svg153, @diegofpb o @frildoren.\n\n";
        $results = $this->getRequest()->markdown()->sendMessage($mensaje);
        return $results;
    }

}