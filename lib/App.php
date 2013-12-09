<?php

/**
 * Editor app
 */
class App extends Phapp
{
	/**
	 * Initialize
	 */
	public function App()
	{
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
