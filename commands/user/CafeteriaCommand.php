<?php

namespace app\commands\user;
use app\commands\base\BaseUserCommand;
use app\models\repositories\CafeteriaRepository;

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
    protected $description = 'Consulta los precios de los productos de cafeteria.';
    protected $usage = '/cafeteria';
    protected $version = '0.5';
    protected $need_mysql = true;
    /**#@-*/

    const KEYBOARD_COLUMNS = 2;

    const CANCEL = 'Cancelar';
    const BACK = 'Atrás';



    /**
     * [process_SelectLine description]
     * @param  [type] $text [description]
     * @return [type]       [description]
     */
    public function processOptions($text)
    {
        $this->getConversation();
        
        $cafetaArray = CafeteriaRepository::getCafetaArray();

        $opts = $this->getCurrentOptions($cafetaArray);

        if(!is_array($opts))
        {
            // We reached last level, send the info.
            $ind = $this->getIndexCurrentOptions();
            $msg = "*".$ind."*\n";
            $msg .= $opts;
            $this->getRequest()->hideKeyboard()->markdown()->sendMessage($msg);
            return $this->previousStep();
        }
        else
        {
            $opts = array_keys($opts);
            $keyboard = array_chunk($opts, self::KEYBOARD_COLUMNS);
            $last = [self::CANCEL];
            if(count($this->getConversation()->notes['indexes']) > 0)
                $last[] = self::BACK;

            $keyboard[] = $last;

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

    private function getCurrentOptions($cafetaArray)
    {
        if(!isset($this->getConversation()->notes['indexes']))
            $this->getConversation()->notes['indexes'] = [];

        $opts = $cafetaArray;
        foreach ($this->getConversation()->notes['indexes'] as $index)
        {
            $opts = $opts[$index];
        }

        return $opts;
    }

    private function getIndexCurrentOptions()
    {
        return $this->getConversation()->notes['indexes'][0];
    }

    function previousStep()
    {
        array_pop($this->getConversation()->notes['indexes']);
        return $this->resetCommand();
    }
}
