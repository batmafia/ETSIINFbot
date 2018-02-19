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

use app\commands\base\BaseUserCommand;
use Longman\TelegramBot\Request;

/**
 * User "/help" command
 */
class HelpCommand extends BaseUserCommand
{
    /**#@+
     * {@inheritdoc}
     */
    protected $name = 'help';
    protected $description = 'Muestra la ayuda de comandos del bot.';
    protected $usage = '/help o /help <comando>';
    protected $version = '1.0.2';
    /**#@-*/

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $message = $this->getMessage();
        $chat_id = $message->getChat()->getId();

        $message_id = $message->getMessageId();
        $command = trim($message->getText(true));

        //Only get enabled Admin and User commands
        $commands = array_filter($this->telegram->getCommandsList(), function ($command) {
            return (!$command->isSystemCommand() && $command->isEnabled());
        });

        //If no command parameter is passed, show the list
        if ($command === '') {
            $text = $this->telegram->getBotName() . ' v. ' . $this->telegram->getVersion() . "\n\n";
            $text .= "Bienvenido al bot de la escuela ". $this->message->getChat()->getFirstName() .
                ", espero serte útil.\n";
            $text .= "Todas vuestras sugerencias o errores que observéis del bot escribidlas a @svg153, @diegofpb o @frildoren.\n\n";
            $text .= 'Aquí tienes la lista de comandos:' . "\n";
            foreach ($commands as $command) {
                $text .= '/' . $command->getName() . ' - ' . $command->getDescription() . "\n";
            }

            $text .= "\n" . 'Para obtener la ayuda de un comando en concreto, escribe: /help <comando>';
        } else {
            $command = str_replace('/', '', $command);
            if (isset($commands[$command])) {
                $command = $commands[$command];
                $text = 'Comando: ' . $command->getName() . ' v' . $command->getVersion() . "\n";
                $text .= 'Descripción: ' . $command->getDescription() . "\n";
                $text .= 'Uso: ' . $command->getUsage();
            } else {
                $text = 'No hay ayuda disponible, el comando /' . $command . ' no ha sido encontrado.';
            }
        }

        $data = [
            'chat_id'             => $chat_id,
            'reply_to_message_id' => $message_id,
            'text'                => $text,
        ];

        return Request::sendMessage($data);
    }
}
