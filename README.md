Linked Data Infrastructure
==========================

Ping a resource and subscribe to its Activity/Histroy/Comment Feed.

Installation
------------
You need a webserver (tested with Apache, but I hope it also runs with nginx and lighttd) and a database backend which is supported by Erfurt (MySQL and Virtuoso).

### Erfurt
Run `git submodule init` and `git submodule update` to clone Erfurt.

Take the prepared `config.ini-dist` file and copy it to `config.ini` and configure it according to your system setup.

### Zend
You have to place a copy of the Zend fraimework library into `libraries/Zend/` you can do this by doing the following things (replace `${ZENDVERSION}` e.g. by `1.11.5`):

    wget http://framework.zend.com/releases/ZendFramework-${ZENDVERSION}/ZendFramework-${ZENDVERSION}-minimal.tar.gz
    tar xzf ZendFramework-${ZENDVERSION}-minimal.tar.gz
    mv ZendFramework-${ZENDVERSION}-minimal/library/Zend libraries
    rm -rf ZendFramework-${ZENDVERSION}-minimal.tar.gz ZendFramework-${ZENDVERSION}-minimal

### Logging
Create an empty log file called `spb.log`

    touch spb.log

Code Conventions
----------------
Currently, this project is developed using [OntoWiki's coding standard](http://code.google.com/p/ontowiki/wiki/CodingStandard).
