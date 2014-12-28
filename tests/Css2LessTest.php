<?php

class Css2LessTest extends PHPUnit_Framework_TestCase
{
    protected function normalizeLineEndings($input)
    {
        $input = str_replace("\r\n", "\n", $input);
        $input = str_replace("\r", "\n", $input);
        $input = preg_replace("/\n{2,}/", "\n\n", $input);

        return $input;
    }

    public function testParseSimpleFile()
    {
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
}
@-moz-keyframes mymozmove{
    from { top: 0px; }
    to { top: 200px; }
}
@keyframes mymove {
    from { top: 0px; }
    to { top: 200px; }
}
EOF;

        $lessContent = <<<EOF
@charset "utf-8";
@font-face {
	font-family: "CrassRoots";
	src: url("../media/cr.ttf");
}
@-moz-keyframes "mymozmove" {
	from {
		top: 0px;
	}
	to {
		top: 200px;
	}
}
@keyframes "mymove" {
	from {
		top: 0px;
	}
	to {
		top: 200px;
	}
}
html {
	font-size: 1.6em;
	body {
		font-size: 1.6em;
	}
	p {
		margin-bottom: 10px;
		margin-top: 10px;
	}
}
@media print {
	#logo {
		hidden: print;
	}
	body {
		#footer {
			height: 50px;
			background: white;
		}
	}
}

EOF;


        $css2lessParser = new \Ortic\Css2Less\Css2Less($cssContent);
        $lessOutput = $css2lessParser->getLess();

        $lessOutput = $this->normalizeLineEndings($lessOutput);
        $lessContent = $this->normalizeLineEndings($lessContent);

        $this->assertEquals($lessOutput, $lessContent);
    }
}
