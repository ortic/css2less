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
            array('html p { font-size: 12px; }', "html {\n\tp {\n\t\tfont-size: 12px;\n\t}\n}\n", false),
            array('html > p { font-size: 12px; }', "html {\n\t>p {\n\t\tfont-size: 12px;\n\t}\n}\n", false),
            array('html> p { font-size: 12px; }', "html {\n\t>p {\n\t\tfont-size: 12px;\n\t}\n}\n", false),
            array('html>p { font-size: 12px; }', "html {\n\t>p {\n\t\tfont-size: 12px;\n\t}\n}\n", false),
            array('html, body { margin: 0; }', "html {\n\tmargin: 0;\n}\nbody {\n\tmargin: 0;\n}\n", false),
            array('a:hover { text-decoration: none; }', "a {\n\t&:hover {\n\t\ttext-decoration: none;\n\t}\n}\n", false),
            array('button::-moz-focus-inner { border: 0; }', "button {\n\t&::-moz-focus-inner {\n\t\tborder: 0;\n\t}\n}\n", false),
            array('a[href^="javascript:"]:after { border: 0; }', "a[href^=\"javascript:\"] {\n\t&:after {\n\t\tborder: 0;\n\t}\n}\n", false),
            array('a { color: white; }', "@color_1: white;\n\na {\n\tcolor: @color_1;\n}\n", true),
            array('a { color: white; } body { background-color: white; }', "@color_1: white;\n@background_color_1: white;\n\na {\n\tcolor: @color_1;\n}\nbody {\n\tbackground-color: @background_color_1;\n}\n", true),
            array('p + p { font-size: 12px; }', "p {\n\t&+p {\n\t\tfont-size: 12px;\n\t}\n}\n", false),
            array('p + p { font-size: 12px; /* width: 200px; */ }', "p {\n\t&+p {\n\t\tfont-size: 12px;\n\t}\n}\n", false),
            array('a ~ p { font-size: 12px; }', "a {\n\t&~p {\n\t\tfont-size: 12px;\n\t}\n}\n", false),
            array('a div[title="a b"] { font-size: 12px; }', "a {\n\tdiv[title=\"a b\"] {\n\t\tfont-size: 12px;\n\t}\n}\n", false),
            array(':not(a) { text-decoration: none; }', ":not(a) {\n\ttext-decoration: none;\n}\n", false),
        );
    }

    /**
     * @dataProvider providerSnippets
     */
    public function testSnippets($css, $less, $extractVariables)
    {
        $css2lessParser = new \Ortic\Css2Less\Css2Less($css);
        $lessOutput = $css2lessParser->getLess($extractVariables);

        $lessOutput = $this->normalizeLineEndings($lessOutput);
        $lessContent = $this->normalizeLineEndings($less);

        $this->assertEquals($lessOutput, $lessContent);
    }

    public function testParseSimpleFile()
    {
        $cssContent = <<<EOF
@charset "utf-8";
/* test comment */
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
