SimsalaPhp
==========

PHP class to compose a web site from a tree of files.

This is a PHP implementation of
[simsalabash](https://github.com/markusfisch/simsalabash).

Sample
------

It's as simple as this:

	$simsala = new Simsala();

	$simsala->contentsDir = 'path/to/the/markdown/files';
	$simsala->layoutsDir = 'path/to/the/layout/files';
	$simsala->htdocsDir = 'where/the/html/files/should/go';

	$simsala->compose();

Layouts
-------

A layout file corresponds to a content file if it has the same name (ignore
extension) or the name of the directory in which the content file is in.

A layout file may look like this:

	<!doctype html>
	<html>
	<head>
	<meta charset="utf-8"/>
	<title><?= $title ?></title>
	<link rel="stylesheet" type="text/css" href="css/screen.css"/>
	</head>
	<body>
	<nav>
	<?= $this->nav() ?>
	</nav>
	<article>
	<?= $content ?>
	</article>
	</body>
	</html>

Markdown
--------

If you want to use [Markdown](https://en.wikipedia.org/wiki/Markdown)
for your content files, you may simply subclass Simsala and use
[PHP Markdown](http://michelf.ca/projects/php-markdown/classic/)
to transform your files into HTML:

	class MarkdownSimsala extends Simsala
	{
		protected function transform( $text )
		{
			require_once( 'markdown.php' );

			return Markdown( $text );
		}
	}
