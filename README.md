### Description

- Sample script/class to sending normal SMTP emails but also delayed emails thanks spool transport from Swifmailer library.

### Requirements
- PHP 8
- Composer 2

### Installation and configuration
- Pull/download files
- Run `php composer install`
- Set up cronjob, to best results every minute:
`* * * * * /cronjob.php`
- For testing just open file ;)
- Configuration (SMTP and time slots) are hardcoded in the config.php file
- For testing, I recommend mailtrap