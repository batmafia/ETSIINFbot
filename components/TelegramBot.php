<?php
namespace app\Components;

use Longman\TelegramBot\ConversationDB;
use Longman\TelegramBot\DB;
use \Longman\TelegramBot\Telegram;
use Yii;
use \yii\base\Configurable;


class TelegramBot extends Telegram  implements Configurable
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

        Yii::$app->db->open();
        $this->dbo = DB::externalInitialize(Yii::$app->db->pdo, $this);
        ConversationDB::initializeConversation();
        $this->mysql_enabled = true;
	}
}
