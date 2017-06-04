Making PHPDoc
-------------

Read this:

    https://github.com/FriendsOfPHP/Sami

Curl this:

    curl -O http://get.sensiolabs.org/sami.phar

Run this:

    rm -r /tmp/tsugi/
    php sami.phar update sami-config-dist.php
    mv /tmp/tsugi/sami.js /tmp/tsugi/s.js
    sed 's/".html"/"index.html"/' < /tmp/tsugi/s.js > /tmp/tsugi/sami.js
    rm /tmp/tsugi/s.js
    open /tmp/tsugi/index.html


