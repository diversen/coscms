cosMail
=======

### About

Simple mail wrapper around PEAR::Mail and PEAR::Mail_Mime

### Configuration

In `config/config.ini` you will write something like this for SMTP

    site_email = "coscms <mail@sweetpoints.dk>"
    smtp_params_host = "mail.coscms.org"
    smtp_params_sender = "mail@coscms.org"
    smtp_params_port = 25
    smtp_params_auth = "login"
    smtp_params_username = "mail@coscms.org"
    smtp_params_password = "password"
    smtp_params_persist = 1

If you have the module `settings` installed, you can set all this from the 
admin interface. 

### Usage

When you have set correct params for mail in `config/config.ini` it is easy
to send mails with attachments and in HTML format. You only need 
the following command: 

    $message = array (
        'txt' => 'message', 
        'html' => '<h3>html message</h3>',                       
        'attachments => array ('/path/to/file', '/path/to/another/file')
    );

    // if you want a diffrent `$reply_to` email then set `$reply_to`
    $reply_to = null;
    $to = mail@example.com
    $subject = 'Hello world';
 
    cosMail::multipart($to, $subject, $message, $reply_to);

Then mail will be send according to the confiuration in `config/config.ini` or
the settings set in the settings module. 

