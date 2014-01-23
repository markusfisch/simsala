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

$app = new App( 'simsala' );

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
