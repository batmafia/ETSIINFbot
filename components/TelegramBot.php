<?php
namespace app\Components;

use Longman\TelegramBot\ConversationDB;
use Longman\TelegramBot\DB;
use \Longman\TelegramBot\Telegram;
use Yii;
use \yii\base\Configurable;


class TelegramBot extends Telegram  implements Configurable
{

	public $token;
	public $name;
    public $admins;

	public function __construct($config = [])
	{
		if (!empty($config)) {
			Yii::configure($this, $config);
		}
		parent::__construct($this->token, $this->name);

        $this->enableAdmins($this->admins);
        $this->addCommands();
        $this->initializeDB();
	}

	private function addCommands()
    {
        $this->addCommandsPath(Yii::$app->basePath."/controllers/userCommands");

        if($this->isAdmin())
        {
            $this->addCommandsPath(Yii::$app->basePath."/controllers/adminCommands");
        }
    }

    private function initializeDB()
    {
        Yii::$app->db->open();
        $this->dbo = DB::externalInitialize(Yii::$app->db->pdo, $this);
        ConversationDB::initializeConversation();
        $this->mysql_enabled = true;
    }
}
