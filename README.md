simsala
=======

Static site editor and generator for the web.

Features
--------

* Generate a static web site from a tree of [Markdown][1] files.
* Automatic generation of a nested site navigation.
* Editor and generator run PHP which means it can be deployed almost anywhere.
* Works on everything with a web browser. Even Opera Mini.

How to use
----------

### Setup

Simply clone this repository into your web root:

	$ git clone https://github.com/markusfisch/simsala.git

Then, create a __contents__ directory within the new simsala directory:

	$ mkdir simsala/contents

Your [Markdown][1] files will go there. To start, you may leave that empty.
Next you need to create a __layouts__ directory in simsala:

	$ mkdir simsala/layouts

Put your layouts into that directory. For example, save this as contents.html
in simsala/layouts:

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

Why contents.html?
A layout file corresponds to a content file if it has the same name or the
name of the directory in which the content file is in.
So the most basic layout is contents.html since all content files are in
the contents folder.

### Set password

Open simsala/index.php with a text editor and find this line:

	$app = new App( 'simsala' );

The word __simsala__ is your password.
Change it to something more secure.

### Log in

Now, open your web site with /simsala in your web browser:

	http://example.com/simsala

You should see a login form. Give your password and press __Login__.

### Edit

Start creating files and folders as you like.
At any time, you may press __Publish__ to generate the HTML files in
the directory that contains simsala.

Here's a [sample repository][2] to get you going.

Advanced use
------------

If you're using git for your project, it's probably better to add a subtree instead of just cloning simsala into your project:

	$ git subtree add --prefix simsala https://github.com/markusfisch/simsala.git master --squash

That way you can always update simsala to the latest version with:

	$ git subtree pull --prefix simsala https://github.com/markusfisch/simsala.git master --squash

[1]:https://en.wikipedia.org/wiki/Markdown
[2]:https://github.com/markusfisch/simsala-sample
