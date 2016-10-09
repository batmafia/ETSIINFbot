<?php
namespace app\Components;

use Longman\TelegramBot\ConversationDB;
use Longman\TelegramBot\DB;
use Longman\TelegramBot\Entities\Update;
use Longman\TelegramBot\Request;
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

        if($this->isModerator() || $this->isAdmin())
        {
            parent::addCommandsPath(Yii::$app->basePath."/commands/moderator");
            if($this->isAdmin())
            {
                parent::addCommandsPath(Yii::$app->basePath."/commands/admin");
            }
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
        $which = ['System','User', 'Moderator', 'Admin'];

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

    public function isModerator($user_id = null)
    {
        if ($user_id === null && $this->update !== null) {
            if (($message = $this->update->getMessage()) && ($from = $message->getFrom())) {
                $user_id = $from->getId();
            } elseif (($inline_query = $this->update->getInlineQuery()) && ($from = $inline_query->getFrom())) {
                $user_id = $from->getId();
            } elseif (($chosen_inline_result = $this->update->getChosenInlineResult()) && ($from = $chosen_inline_result->getFrom())) {
                $user_id = $from->getId();
            } elseif (($callback_query = $this->update->getCallbackQuery()) && ($from = $callback_query->getFrom())) {
                $user_id = $from->getId();
            } elseif (($edited_message = $this->update->getEditedMessage()) && ($from = $edited_message->getFrom())) {
                $user_id = $from->getId();
            }
        }

        return ($user_id === null) ? false : in_array($user_id, Yii::$app->params['moderators']);
    }

    public function handleGetUpdates($limit = null, $timeout = null)
    {
        if (!DB::isDbConnected()) {
            return new \Longman\TelegramBot\Entities\ServerResponse([
                'ok'          => false,
                'description' => 'getUpdates needs MySQL connection!',
            ], $this->bot_name);
        }

        //DB Query
        $last_update = DB::selectTelegramUpdate(1);

        //As explained in the telegram bot api documentation
        $offset = (isset($last_update[0]['id'])) ? $last_update[0]['id'] + 1 : null;

        $response = Request::getUpdates([
            'offset'  => $offset,
            'limit'   => $limit,
            'timeout' => $timeout,
        ]);

        $ok = $response->isOk();

        if ($ok) {
            //Process all updates
            foreach ((array) $response->getResult() as $result) {
                $ok &= $this->processUpdate($result)->isOk();
            }
        }

        return $response;
    }

}
