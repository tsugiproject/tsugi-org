
How to Check Out all the Pieces of PHP Tsugi
============================================

This is a quick set of steps to check out all of the separate pieces
of Tsugi locally.  You need to know where your `htdocs` folder is:

    cd htdocs
    git clone https://github.com/tsugiproject/tsugi
    cd tsugi

Run through the configuration - make sure tsugi comes up and run
Admin / Database Upgrade.

Add the Tools
-------------

    cd htdocs/tsugi
    git clone https://github.com/tsugiproject/tsugi-php-mod mod
    cd ..  (back in htdocs)
    git clone https://github.com/tsugiproject/tsugi-static
    git clone https://github.com/tsugiproject/tsugi-php-samples
    git clone https://github.com/tsugiproject/tsugi-php-exercises
    git clone https://github.com/tsugiproject/tsugi-php-module
    git clone https://github.com/tsugiproject/tsugi-php-standalone

Then in `htdocs/tsugi/config.php` adjust the following values:

    // Serve static content locally instead of the CDN
    $CFG->staticroot = "../tsugi-static";

    $CFG->tool_folders = array("admin", "mod",
        "../tsugi-php-standalone", "../tsugi-php-module",
        "../tsugi-php-samples", "../tsugi-php-exercises");

