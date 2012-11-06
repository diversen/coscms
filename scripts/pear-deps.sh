#!/bin/sh

# for pagination
pear install Pager

# for console usage
pear install Console_Commandline

# for email validation and more. 
pear install Validate-0.8.4

# for sending mails
pear install Mail

# for sending mime mails
pear install Mail_Mime

# for image transformation
pear install Image_Transform-0.9.3

# for smtp emailing
pear install Net_SMTP

# for using markdown
pear channel-discover pear.michelf.com
pear install michelf/MarkdownExtra

# For automatic documentation. Install:
# pear channel-discover pear.phpdoc.org
# pear install phpdoc/phpDocumentor-alpha
# pear install phpdoc/phpDocumentor-alpha

# for aksimet
# pear install Net_URL2-0.3.1
# pear install HTTP_Request2-0.5.2
# pear install Services_Akismet2-0.3.1
