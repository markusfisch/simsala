<?php

/**
 * Abstract base view
 */
class View extends PhappView
{
	/**
	 * Process requests
	 */
	public function request()
	{
		if( $_REQUEST['password'] == $this->app->password )
			$_SESSION['logged_in'] = true;

		if( $_SESSION['logged_in'] )
			// show requested view
			return null;

		return 'LoginView';
	}

	/**
	 * Translate a label
	 *
	 * @param $en - english label to translate
	 */
	protected function tr( $en )
	{
		// overwrite this method to implement i18n
		return $en;
	}
}
