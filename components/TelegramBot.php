<?php
namespace app\Components;

use Longman\TelegramBot\ConversationDB;
use Longman\TelegramBot\DB;
use Longman\TelegramBot\Entities\Update;
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
        $this->version = Yii::$app->params['version'];

        $this->enableAdmins($this->admins);
        $this->addCommands();
        $this->initializeDB();
	}

	private function addCommands()
    {
        $this->commands_paths = [];

        require_once Yii::$app->basePath."/commands/base/BaseCommand.php";
        require_once Yii::$app->basePath."/commands/base/BaseRegularCommand.php";
        foreach (glob(Yii::$app->basePath."/commands/base/*.php") as $filename)
        {
            require_once $filename;
        }

        parent::addCommandsPath(Yii::$app->basePath."/commands/system");
        parent::addCommandsPath(Yii::$app->basePath."/commands/user");

        if($this->isAdmin())
        {
            parent::addCommandsPath(Yii::$app->basePath."/commands/admin");
        }
    }

    private function initializeDB()
    {
        Yii::$app->db->open();
        $this->dbo = DB::externalInitialize(Yii::$app->db->pdo, $this);
        ConversationDB::initializeConversation();
        $this->mysql_enabled = true;
    }

    public function getCommandObject($command)
    {
        $which = ['System'];
        ($this->isAdmin()) && $which[] = 'Admin';
        $which[] = 'User';

        foreach ($which as $auth) {
            $command_namespace =  'app\commands\\' . $auth . '\\' . $this->ucfirstUnicode($command) . 'Command';
            if (class_exists($command_namespace)) {
                return new $command_namespace($this, $this->update);
            }
        }

        return null;
    }


    public function addCommandsPath($path, $before = true)
    {
        return $this;
    }


    public function processUpdate(Update $update)
    {
        $this->update = $update;
        $this->addCommands();
        return parent::processUpdate($update);
    }

}
