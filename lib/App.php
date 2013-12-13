<?php

/**
 * Editor app
 */
class App extends Phapp
{
	/** Passphrase */
	public $password;

	/** Simsala instance */
	public $simsala;

	/**
	 * Initialize
	 *
	 * @param $password - password
	 */
	public function App( $password )
	{
		$this->password = $password;

		$this->simsala = new MarkdownSimsala();
		$this->simsala->htdocsDir = '..';
	}

	/**
	 * Return title
	 */
	public function title()
	{
		return 'Edit ' . $_SERVER['SERVER_NAME'];
	}

	/**
	 * Generate page contents
	 */
	public function contents()
	{
		return $this->process( 'EditorView' );
	}
}
