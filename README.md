Phapp
=====

A simple but scalable object-oriented web application framework.

Hello World
-----------

A simple example:

	<?php

	require_once 'Phapp.php';

	class Hello extends PhappView
	{
		public function response()
		{
			return 'Hello World.';
		}
	}

	$app = new Phapp();

	echo $app->process( 'Hello' );

Find this and more complex examples in the
[samples branch](https://github.com/markusfisch/Phapp/tree/samples).

How it works
------------

The idea is to define a set of view objects that get processed by the main
application object.

Every view object inherits from PhappView which has two main methods you
should overwrite. The first is request():

	public function request()
	{
		if( !$_REQUEST['order_number'] )
			return 'Home';

		return null;
	}

The purpose of this method is to handle the request and, if required, return
a name of another view that should take over.

The first view that returns _null_ will get its response() method called to
respond to the request:

	public function response()
	{
		return '<h1>Hello</h1>';
	}

Here's a little graph that shows how everything works together:

                +----------------------------+
                | Name of a PhappView object | <-------------------------+
                +----------------------------+                           |
                    |                                                    |
                    |                                                    |
    +-- Phapp ------|-------+                                            |
    |               |       |                                            |
    |               V       |                                            |
    | +------------------------+           +-- PhappView ---------+      |
    | |    Construct object    |  ------>  |                      |      |
    | +------------------------+           |    Object derived    |      |
    |               |       |              |    from PhappView    |      |
    |               V       |              |                      |      |
    | +------------------------+           +-- request() ---------+      |
    | |    Call request() on   |           |                      |      |
    | |       that object      |  ------>  |     Determine if     |      |
    | +------------------------+           |      this object     |      |
    |                       |              |      can handle      |      |
    | +------------------------+    yes?   |     this request     |  no? |
    | |   request() returned   |  <------  |                      | -----+
    | |          null          |           +-- response() --------+
    | |   so call response()   |  ------>  |                      |
    | |    to get the output   |  <------  |  Generate output for |
    | +------------------------+    html   |     this request     |
    |               |       |              |                      |
    +---------------|-------+              +----------------------+
                    |
                    V
               +----------+
               | Web page |
               +----------+

Phapp is very minimal and very small.
Just have a look at Phapp.php to fully understand its concept.

Subclass to extend
------------------

Subclass Phapp and PhappView to add methods for data base access,
internationalization and stuff like that.

It's a good idea to set up a base view which contains common methods
and derive your views from that.

PDO support
-----------

If you're using [PDO](http://php.net/manual/en/book.pdo.php)'s,
you may inherit your views from PhappPDOView and call query() like this:

	if( ($result = $this->query(
		"SELECT
			first,
			last
			FROM members
			WHERE last_login = ?
			ORDER BY last",
		$lastLogin )) )
	{
		while( ($row = $result->fetch()) )
		{
			$contents .= "<li>{$row['first']} {$row['last']}</li>";
		}
	}

Stay up to date
---------------

It's probably best to add Phapp as submodule or
[subtree](https://blogs.atlassian.com/2013/05/alternatives-to-git-submodule-git-subtree/)
to your project's repository to be able to update fast and easily.
