<?php

namespace app\commands\base;

use Longman\TelegramBot\DB;
use Longman\TelegramBot\Entities\Update;
use Longman\TelegramBot\Telegram;


abstract class BaseRegularCommand extends BaseCommand
{

    private $conversation;
    private $processed = false;
    private $request;

    function __construct(Telegram $telegram, Update $update)
    {
        parent::__construct($telegram, $update);

        $this->request = new Request($this->getMessage()->getChat()->getId());
    }


    function execute()
    {
        $conversation = new Conversation($this->getMessage()->getFrom()->getId(), $this->getMessage()->getChat()->getId(), $this->getName());
        if(!isset($this->conversation) && $conversation->exists())
            $this->conversation = $conversation;

        $step = $this->getStepIndex();
        $executes = array_values(array_filter(get_class_methods(get_class($this)), function($name)
        {
            return substr($name, 0, 7) === 'process';
        }));

        $args = [];
        $reflection = new \ReflectionMethod(get_class($this), $executes[intval($step)]);
        foreach($reflection->getParameters() as $parameter)
        {
            switch($parameter->getName())
            {
                case 'text':
                    $args[] = $this->getMessage()->getText(true);
                    break;
                default:
                    $args[] = $this->getMessage()->{"get".ucfirst($parameter->getName())}();
                    break;
            }
        }
        $result = call_user_func_array([$this, $executes[intval($step)]], $args);

        if(isset($this->conversation))
            $this->conversation->update();

        return $result;
    }

    private function resetCommand()
    {
        $this->processed = true;
        return $this->preExecute();
    }

    public function nextStep()
    {
        $this->setStepIndex($this->getStepIndex()+1);
        return $this->resetCommand();
    }

    public function previousStep()
    {
        $this->setStepIndex($this->getStepIndex()-1);
        return $this->resetCommand();
    }

    private function setStepIndex($index)
    {
        $index = intval($index);

        if($index >= 0)
            $this->conversation->notes['step_index'] = $index;
    }

    private function getStepIndex()
    {
        if(!isset($this->conversation->notes['step_index']))
            return 0;

        return $this->conversation->notes['step_index'];
    }

    /**
     * @return Request
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * @param Conversation $conversation
     */
    public function getConversation()
    {
        if(!isset($this->conversation))
            $this->conversation = new Conversation($this->getMessage()->getFrom()->getId(), $this->getMessage()->getChat()->getId(), $this->getName());

        return $this->conversation;
    }

    public function stopConversation()
    {
        $this->conversation->stop();
        $this->conversation = null;
    }

    public function setConversation($conversation)
    {
        $this->conversation = $conversation;
    }

    public function isProcessed()
    {
        return $this->processed;
    }

    public function isAdminCommand()
    {
        return false;
    }

    public function isUserCommand()
    {
        return false;
    }

    public function isSystemCommand()
    {
        return false;
    }

}