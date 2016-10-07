<?php

namespace app\models\core;

use Yii;

/**
 * This is the model class for table "user".
 *
 * @property integer $id
 * @property string $first_name
 * @property string $last_name
 * @property string $username
 * @property string $created_at
 * @property string $updated_at
 * @property integer $broadcast
 *
 * @property BotanShortener[] $botanShorteners
 * @property CallbackQuery[] $callbackQueries
 * @property ChosenInlineResult[] $chosenInlineResults
 * @property Conversation[] $conversations
 * @property EditedMessage[] $editedMessages
 * @property InlineQuery[] $inlineQueries
 * @property Message[] $messages
 * @property Message[] $messages0
 * @property Message[] $messages1
 * @property Message[] $messages2
 * @property Message[] $messages3
 * @property UserChat[] $userChats
 * @property Chat[] $chats
 */
class User extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'user';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id'], 'required'],
            [['id', 'broadcast'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['first_name', 'last_name', 'username'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'first_name' => 'First Name',
            'last_name' => 'Last Name',
            'username' => 'Username',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'broadcast' => 'Broadcast',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getBotanShorteners()
    {
        return $this->hasMany(BotanShortener::className(), ['user_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCallbackQueries()
    {
        return $this->hasMany(CallbackQuery::className(), ['user_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getChosenInlineResults()
    {
        return $this->hasMany(ChosenInlineResult::className(), ['user_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getConversations()
    {
        return $this->hasMany(Conversation::className(), ['user_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getEditedMessages()
    {
        return $this->hasMany(EditedMessage::className(), ['user_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getInlineQueries()
    {
        return $this->hasMany(InlineQuery::className(), ['user_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getMessages()
    {
        return $this->hasMany(Message::className(), ['user_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getMessages0()
    {
        return $this->hasMany(Message::className(), ['forward_from' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getMessages1()
    {
        return $this->hasMany(Message::className(), ['forward_from' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getMessages2()
    {
        return $this->hasMany(Message::className(), ['new_chat_member' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getMessages3()
    {
        return $this->hasMany(Message::className(), ['left_chat_member' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUserChats()
    {
        return $this->hasMany(UserChat::className(), ['user_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getChats()
    {
        return $this->hasMany(Chat::className(), ['id' => 'chat_id'])->viaTable('user_chat', ['user_id' => 'id']);
    }

    /**
     * @inheritdoc
     * @return UserQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new UserQuery(get_called_class());
    }
}
