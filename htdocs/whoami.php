<?php

// whoami. Determine which user the apache2 is running as
// e.g. www-data
echo getenv('APACHE_RUN_USER');
