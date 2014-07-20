<?php

ini_set( 'session.use_cookies', 0 );
ini_set( 'session.use_only_cookies', 0 );
ini_set( 'session.use_trans_sid', 1 );

session_start();

set_include_path( 'lib/Phapp:lib/SimsalaPhp:lib/Simsala:lib/php-markdown' );

function __autoload( $cls )
{
	require_once $cls . '.php';
}

$app = new App(
	// MD5 sum of your password
	'c166638736413e6136fdf3b5aea1c419' );

?>
<!doctype html>
<html>
<head>
<meta charset="utf-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1"/>
<title><?= $app->title() ?></title>
<link rel="stylesheet" type="text/css" href="css/screen.css"/>
</head>
<body>
<?= $app->contents() ?>
</body>
</html>
