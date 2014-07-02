<?php

include __DIR__ . '/vendor/autoload.php';

$cssContent = <<<EOF
@charset "utf-8";

@font-face {
  font-family: "CrassRoots";
  src: url("../media/cr.ttf")
}

html, body {
    font-size: 1.6em
}

html p {
    margin-bottom: 10px;
    margin-top: 10px;
}

@media print {
    #logo {
        hidden: print;
    }
    body #footer {
        height: 50px;
        background: white;
    }
    p {
        @font-face {
            font-family: "CrassRoots";
        }
    }
}

@keyframes mymove {
    from { top: 0px; }
    to { top: 200px; }
}
EOF;

//$cssContent = 'body {height: 100%;} body p {margin-top: 20px;margin-bottom: 20px;} body h1 {font-size: 20px;} @media print { p, blockquote {font-family: arial;} }';

/*
$css2lessParser = new \Ortic\Css2Less\Css2Less($cssContent);
$lessTree = $css2lessParser->parse();
echo $css2lessParser->formatAsLess($lessTree);
*/

$css2lessParser = new \Ortic\Css2Less\Css2Less2($cssContent);
echo $css2lessParser->getLess();

