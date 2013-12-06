<?php

set_include_path( 'lib:lib/SimsalaPhp:lib/Phapp:lib/php-markdown' );

function __autoload( $cls )
{
	require_once $cls . '.php';
}

$app = new App();

?>
<!doctype html>
<html>
<head>
<meta charset="utf-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0"/>
<title><?= $app->title() ?></title>
<link rel="stylesheet" type="text/css" href="css/screen.css"/>
</head>
<body>
<?= $app->content() ?>
</body>
</html>
