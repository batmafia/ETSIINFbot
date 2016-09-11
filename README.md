# ETSIINFbot

### Installation

* Clone repository

        git clone https://github.com/svg153/ETSIINFbot.git
        
* Update dependencies
    - move to ETSIINFbot folder
        composer global require "fxp/composer-asset-plugin:^1.2.0"
        php composer.phar update
    
* Dependencies problems
    - longman/telegram-bot 0.35 requires ext-curl * -> the requested PHP extension curl is missing from your system.
        sudo apt-get install php-curl

    - yiisoft/yii2 2.0.9 requires ext-mbstring * -> the requested PHP extension mbstring is missing from your system.
        sudo apt-get install php-mbstring
        
    - Exception 'yii\db\Exception' with message 'could not find driver'        
        sudo apt-get install php-mysql
        
* Set your local config
    
    - Copy config/db.php.example to config/db.php and edit with your DB values
        cp config/db.php.example config/db.php
        
    - Copy config/bot.php.example to bot.php and edit with your bot values
        cp config/bot.php.example config/bot.php
        
* Run migration (actually needed everytime a feature updates the DB)

        ./yii migrate
        
        
        
        
### Running

In production environments it is recommended to use WebHook, but for testing you can use getupdates method.

* Using getUpdates()

        ./yii start/updates
