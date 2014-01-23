<?php

/**
 * Login view
 */
class LoginView extends View
{
	/**
	 * Process requests
	 */
	public function request()
	{
		// don't call View::request() here, this view is public
		return null;
	}

	/**
	 * Generate HTML response
	 */
	public function response()
	{
		return <<<EOF
<form action="?" method="post">
<div class="Login">
<input type="password" class="Passphrase" name="password"
placeholder="{$this->tr( 'Enter passphrase' )}"/>
<input type="submit" class="Button" name="login"
value="{$this->tr( 'Login' )}"/>
</div>
</form>\n
EOF;
	}
}
