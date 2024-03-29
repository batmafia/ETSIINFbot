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

        $this->request = new Request;

        if($this->getMessage() !== null && $this->getMessage()->getChat() !== null)
        {
            $this->request->chatId($this->getMessage()->getChat()->getId());
            if($this->getMessage()->getChat()->isGroupChat() || $this->getMessage()->getChat()->isSuperGroup())
                $this->request->replyTo($this->getMessage()->getMessageId());
        }

    }


    function execute()
    {
        $conversation = new Conversation($this->getMessage()->getFrom()->getId(), $this->getMessage()->getChat()->getId(), $this->getName());
        if(!isset($this->conversation) && $conversation->exists())
            $this->conversation = $conversation;


        $step = $this->getStepIndex();
        $prefix = 'process'.implode(array_map("ucfirst", $this->getStepBranches()));
        $executes = array_values(array_filter(get_class_methods(get_class($this)), function($name) use($prefix)
        {
            return substr($name, 0, strlen($prefix)) === $prefix;
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
                case 'message':
                    $args[] = $this->getMessage();
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

    public function resetCommand()
    {
        $this->processed = true;
        return $this->preExecute();
    }

    public function nextStep($branch=null)
    {
        if($branch !== null)
        {
            $branches = $this->getStepBranches();
            $branches[] = $branch;
            $this->setStepBranches($branches);
            $this->getConversation()->notes['stack_frame'][] = $this->getStepIndex();
            $this->setStepIndex(0);
        }
        else
        {
            $this->setStepIndex($this->getStepIndex() + 1);
        }

        return $this->resetCommand();
    }

    public function previousStep()
    {
        if($this->getStepIndex() === 0)
        {
            $branch = $this->getStepBranches();
            array_pop($branch);
            $this->setStepBranches($branch);
            $this->setStepIndex(end($this->getConversation()->notes['stack_frame']));
        }
        else
        {
            $this->setStepIndex($this->getStepIndex()-1);
        }
        return $this->resetCommand();
    }

    private function setStepIndex($index)
    {
        $index = intval($index);

        if($index >= 0)
            $this->getConversation()->notes['step_index'] = $index;
    }

    private function getStepIndex()
    {
        if(!isset($this->conversation) || !isset($this->getConversation()->notes['step_index']))
            return 0;

        return $this->getConversation()->notes['step_index'];
    }

    private function getStepBranches()
    {
        if(!isset($this->conversation) || !isset($this->getConversation()->notes['step_branch']))
            return [];

        return $this->getConversation()->notes['step_branch'];
    }

    private function setStepBranches($branches)
    {
        $this->getConversation()->notes['step_branch'] = $branches;
    }

    public function cancelConversation()
    {
        $msgCancelConver = "*Comando cancelado.*\n".
                            "Más comandos en /help.";

        $this->stopConversation();
        return $this->getRequest()->hideKeyboard()->markdown()->sendMessage($msgCancelConver);
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