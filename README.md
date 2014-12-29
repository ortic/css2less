[![Build Status](https://api.travis-ci.org/ortic/css2less.svg?branch=master)](https://travis-ci.org/ortic/css2less)
[![Code Rating](https://img.shields.io/scrutinizer/g/ortic/css2less.svg?style=flat)](https://scrutinizer-ci.com/g/ortic/css2less/)
[![Coverage Status](https://img.shields.io/scrutinizer/coverage/g/ortic/css2less.svg?style=flat)](https://scrutinizer-ci.com/g/ortic/css2less/)

css2less
========

this library aims to convert CSS files into LESS files.

Currently used by http://www.css2less.net/

example
=======

The code below takes a few CSS instructions and prints them in a more LESS like form:

```php
$cssContent = 'body p { font-family: arial; }';
$css2lessParser = new \Ortic\Css2Less\Css2Less($cssContent);
echo $css2lessParser->getLess();
```

output:

```
body {
        p {
                font-family: arial;
        }
}
```