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
	 * Generate content
	 */
	public function content()
	{
		return $this->process( 'EditorView' );
	}
}
