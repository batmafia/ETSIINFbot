<?php

namespace app\commands\user;
use app\commands\base\BaseUserCommand;
use app\models\repositories\InfoRepository;

/**
 * User "/info" command
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

    const CANCEL = 'Cancelar';
    const BACK = 'Atrás';

    /**
     * [process_SelectLine description]
     * @param  [type] $text [description]
     * @return [type]       [description]
     */
    public function processOptions($text)
    {
        $infoArray = InfoRepository::getInfoArray();

        $opts = $this->getCurrentOptions($infoArray);

        if(!is_array($opts))
        {
            //We reached last level, send the info.
            $this->getRequest()->hideKeyboard()->markdown()->sendMessage($opts);
            return $this->previousStep();
        }
        else
        {
            $opts = array_keys($opts);
            $keyboard = array_chunk($opts, self::KEYBOARD_COLUMNS);
            $keyboard[] = [self::CANCEL, self::BACK];

            $this->getRequest()->keyboard($keyboard);

            if ($this->isProcessed() || empty($text)) {
                return $this->getRequest()->sendMessage('Selecciona una opción');
            }

            if($text === self::BACK)
            {
                return $this->previousStep();
            }

            if ($text === self::CANCEL)
            {
                return $this->cancelConversation();
            }

            if (!(in_array($text, $opts)))
            {
                return $this->getRequest()->sendMessage('Selecciona una opción del teclado por favor:');
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

    function previousStep()
    {
        array_pop($this->getConversation()->notes['indexes']);
        return $this->resetCommand();
    }
}
