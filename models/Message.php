<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "message".
 *
 * @property integer $chat_id
 * @property string $id
 * @property integer $user_id
 * @property string $date
 * @property integer $forward_from
 * @property integer $forward_from_chat
 * @property string $forward_date
 * @property integer $reply_to_chat
 * @property string $reply_to_message
 * @property string $text
 * @property string $entities
 * @property string $audio
 * @property string $document
 * @property string $photo
 * @property string $sticker
 * @property string $video
 * @property string $voice
 * @property string $contact
 * @property string $location
 * @property string $venue
 * @property string $caption
 * @property integer $new_chat_member
 * @property integer $left_chat_member
 * @property string $new_chat_title
 * @property string $new_chat_photo
 * @property integer $delete_chat_photo
 * @property integer $group_chat_created
 * @property integer $supergroup_chat_created
 * @property integer $channel_chat_created
 * @property integer $migrate_to_chat_id
 * @property integer $migrate_from_chat_id
 * @property string $pinned_message
 *
 * @property CallbackQuery[] $callbackQueries
 * @property EditedMessage[] $editedMessages
 * @property User $user
 * @property Chat $chat
 * @property User $forwardFrom
 * @property Chat $forwardFromChat
 * @property Message $replyToChat
 * @property Message[] $messages
 * @property User $forwardFrom0
 * @property User $newChatMember
 * @property User $leftChatMember
 * @property TelegramUpdate[] $telegramUpdates
 */
class Message extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'message';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['chat_id', 'id'], 'required'],
            [['chat_id', 'id', 'user_id', 'forward_from', 'forward_from_chat', 'reply_to_chat', 'reply_to_message', 'new_chat_member', 'left_chat_member', 'delete_chat_photo', 'group_chat_created', 'supergroup_chat_created', 'channel_chat_created', 'migrate_to_chat_id', 'migrate_from_chat_id'], 'integer'],
            [['date', 'forward_date'], 'safe'],
            [['text', 'entities', 'audio', 'document', 'photo', 'sticker', 'video', 'voice', 'contact', 'location', 'venue', 'caption', 'new_chat_photo', 'pinned_message'], 'string'],
            [['new_chat_title'], 'string', 'max' => 255],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['user_id' => 'id']],
            [['chat_id'], 'exist', 'skipOnError' => true, 'targetClass' => Chat::className(), 'targetAttribute' => ['chat_id' => 'id']],
            [['forward_from'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['forward_from' => 'id']],
            [['forward_from_chat'], 'exist', 'skipOnError' => true, 'targetClass' => Chat::className(), 'targetAttribute' => ['forward_from_chat' => 'id']],
            [['reply_to_chat', 'reply_to_message'], 'exist', 'skipOnError' => true, 'targetClass' => Message::className(), 'targetAttribute' => ['reply_to_chat' => 'chat_id', 'reply_to_message' => 'id']],
            [['forward_from'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['forward_from' => 'id']],
            [['new_chat_member'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['new_chat_member' => 'id']],
            [['left_chat_member'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['left_chat_member' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'chat_id' => 'Chat ID',
            'id' => 'ID',
            'user_id' => 'User ID',
            'date' => 'Date',
            'forward_from' => 'Forward From',
            'forward_from_chat' => 'Forward From Chat',
            'forward_date' => 'Forward Date',
            'reply_to_chat' => 'Reply To Chat',
            'reply_to_message' => 'Reply To Message',
            'text' => 'Text',
            'entities' => 'Entities',
            'audio' => 'Audio',
            'document' => 'Document',
            'photo' => 'Photo',
            'sticker' => 'Sticker',
            'video' => 'Video',
            'voice' => 'Voice',
            'contact' => 'Contact',
            'location' => 'Location',
            'venue' => 'Venue',
            'caption' => 'Caption',
            'new_chat_member' => 'New Chat Member',
            'left_chat_member' => 'Left Chat Member',
            'new_chat_title' => 'New Chat Title',
            'new_chat_photo' => 'New Chat Photo',
            'delete_chat_photo' => 'Delete Chat Photo',
            'group_chat_created' => 'Group Chat Created',
            'supergroup_chat_created' => 'Supergroup Chat Created',
            'channel_chat_created' => 'Channel Chat Created',
            'migrate_to_chat_id' => 'Migrate To Chat ID',
            'migrate_from_chat_id' => 'Migrate From Chat ID',
            'pinned_message' => 'Pinned Message',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCallbackQueries()
    {
        return $this->hasMany(CallbackQuery::className(), ['chat_id' => 'chat_id', 'message_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getEditedMessages()
    {
        return $this->hasMany(EditedMessage::className(), ['chat_id' => 'chat_id', 'message_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getChat()
    {
        return $this->hasOne(Chat::className(), ['id' => 'chat_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getForwardFrom()
    {
        return $this->hasOne(User::className(), ['id' => 'forward_from']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getForwardFromChat()
    {
        return $this->hasOne(Chat::className(), ['id' => 'forward_from_chat']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getReplyToChat()
    {
        return $this->hasOne(Message::className(), ['chat_id' => 'reply_to_chat', 'id' => 'reply_to_message']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getMessages()
    {
        return $this->hasMany(Message::className(), ['reply_to_chat' => 'chat_id', 'reply_to_message' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getForwardFrom0()
    {
        return $this->hasOne(User::className(), ['id' => 'forward_from']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getNewChatMember()
    {
        return $this->hasOne(User::className(), ['id' => 'new_chat_member']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLeftChatMember()
    {
        return $this->hasOne(User::className(), ['id' => 'left_chat_member']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTelegramUpdates()
    {
        return $this->hasMany(TelegramUpdate::className(), ['chat_id' => 'chat_id', 'message_id' => 'id']);
    }

    /**
     * @inheritdoc
     * @return MessageQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new MessageQuery(get_called_class());
    }
}
