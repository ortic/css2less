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

    public function providerSnippets()
    {
        return array(
            array('html p { font-size: 12px; }', "html {\n\tp {\n\t\tfont-size: 12px;\n\t}\n}\n"),
            array('html, body { margin: 0; }', "html {\n\tmargin: 0;\n}\nbody {\n\tmargin: 0;\n}\n"),
        );
    }

    /**
     * @dataProvider providerSnippets
     */
    public function testSnippets($css, $less)
    {
        $css2lessParser = new \Ortic\Css2Less\Css2Less($css);
        $lessOutput = $css2lessParser->getLess();

        $lessOutput = $this->normalizeLineEndings($lessOutput);
        $lessContent = $this->normalizeLineEndings($less);

        $this->assertEquals($lessOutput, $lessContent);
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
	p {
		margin-bottom: 10px;
		margin-top: 10px;
	}
}
body {
	font-size: 1.6em;
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
