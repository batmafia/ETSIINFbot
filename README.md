# ETSIINFbot

### Installation

* Clone repository

        git clone https://github.com/svg153/ETSIINFbot.git
        
* Update dependencies
 
        composer global require "fxp/composer-asset-plugin:^1.2.0"
        php composer.phar update
        
* Set your local config

        Copy config/db.php.example to config/db.php and edit with your DB values
        Copy config/bot.php.example to bot.php and edit with your bot values
        
* Run migration (actually needed everytime a feature updates the DB)

        ./yii migrate
        
        
        
        
### Running

In production environments it is recommended to use WebHook, but for testing you can use getupdates method.

* Using getUpdates()

        ./yii start/updates