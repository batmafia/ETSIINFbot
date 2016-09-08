<?php
namespace app\Components;

use Yii;
use Exception;
use \yii\base\Configurable;
use \Longman\TelegramBot;


class Component extends Telegram implements Configurable
{
	/**
	 * Bot token
	 * @var string
	 * Bot name
	 * @var string
	 */
	public $token;
	public $name;
	public function __construct($config = [])
	{
		if (!empty($config)) {
			Yii::configure($this, $config);
		}
		parent::__construct($this->token, $this->name);
	}
}
