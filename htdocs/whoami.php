<?php

// whoami. Determine which user the apache2 is running as
echo getenv('APACHE_RUN_USER');
