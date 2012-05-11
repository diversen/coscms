#!/bin/sh
# Install some usefull pear packages

pear install Pager
pear install Console_Commandline
pear install Text_Highlighter-0.7.1
pear install Validate-0.8.4
pear install Mail
pear install Mail_Mime
pear install HTML_Safe-0.10.1
pear install Image_Transform-0.9.3
pear install Net_SMTP
pear install Cache_Lite
pear install Console_Color

# install markdown
# (used in a couple of filters)
pear channel-discover pear.michelf.com
pear install michelf/MarkdownExtra

# Pearhub.org seems to be broken sometimes ...
# (account/facebook)
# install facebook php-sdk
pear channel-discover pearhub.org
pear install pearhub/facebook

# For automatic documentation. Install:
pear install PhpDocumentor

# for aksimet
pear install Net_URL2-0.3.1
pear install HTTP_Request2-0.5.2
pear install Services_Akismet2-0.3.1
