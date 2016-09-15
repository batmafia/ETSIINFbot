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

/**
 * User "/survery" command
 */
class SurveyCommand extends BaseUserCommand
{
    /**#@+
     * {@inheritdoc}
     */
    public $enabled = false;

    protected $name = 'survey';
    protected $description = 'Survery for bot users';
    protected $usage = '/survey';
    protected $version = '0.2.0';
    protected $need_mysql = true;
    /**#@-*/


    /**
     * {@inheritdoc}
     */
    public function processName($text)
    {
        if (empty($text))
        {
            $this->getConversation();
            return $this->getRequest()->sendMessage('Type your name:');
        }

        $this->getConversation()->notes['name'] = $text;
        return $this->nextStep();
    }

    public function processSurname($text)
    {
        if ($this->isProcessed() || empty($text))
        {
            return $this->getRequest()->sendMessage('Type your surname:');
        }

        $this->getConversation()->notes['surname'] = $text;
        return $this->nextStep();
    }

    public function processAge($text)
    {
        if ($this->isProcessed() || empty($text))
        {
            return $this->getRequest()->sendMessage('Type your age:');
        }

        if(!is_numeric($text))
        {
            return $this->getRequest()->sendMessage('Type your age, must be a number');
        }

        $this->getConversation()->notes['age'] = $text;
        return $this->nextStep();
    }

    public function processSex($text)
    {
        $this->getRequest()->keyboard([['M','F']]);

        if ($this->isProcessed() || empty($text))
        {
            return $this->getRequest()->sendMessage('Select your gender:');
        }

        if(!($text == 'M' || $text == 'F'))
        {
            return $this->getRequest()->sendMessage('Select your gender, choose a keyboard option:');
        }

        $this->getConversation()->notes['gender'] = $text;
        return $this->nextStep();
    }

    public function processLocation($location)
    {
        if ($this->isProcessed() || is_null($location)) {
            return $this->getRequest()->locationKeyboard()->sendMessage('Share your location:');
        }

        $this->getConversation()->notes['longitude'] = $location->getLongitude();
        $this->getConversation()->notes['latitude'] = $location->getLatitude();
        return $this->nextStep();
    }

    public function processPhoto($photo)
    {
        if ($this->isProcessed() || is_null($photo)) {
            return $this->getRequest()->hideKeyboard()->sendMessage('Insert your picture:');
        }

        $this->getConversation()->notes['photo_id'] = $photo[0]->getFileId();
        return $this->nextStep();
    }

    public function processContact($contact)
    {
        if ($this->isProcessed() || is_null($contact))
        {
            return $this->getRequest()->contactKeyboard()->sendMessage('Share your contact information:');
        }

        $this->getConversation()->notes['phone_number'] = $contact->getPhoneNumber();
        return $this->nextStep();
    }

    public function processResult()
    {
        $out_text = '/Survey result:' . "\n";
        foreach (['name','surname','age','gender','longitude','latitude','phone_number'] as $k) {
            $out_text .= "\n" . ucfirst($k).': ' . $this->getConversation()->notes[$k];
        }

        $result = $this->getRequest()->hideKeyboard()->sendPhoto($this->getConversation()->notes['photo_id'], $out_text);
        $this->stopConversation();

        return $result;
    }
}
