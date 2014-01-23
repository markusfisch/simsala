<?php

/**
 * Compose a web site from a tree of markdown files
 */
class MarkdownSimsala extends Simsala
{
	/**
	 * Transform input text
	 *
	 * @param $text - text to transform
	 */
	protected function transform( $text )
	{
		require_once( 'markdown.php' );

		return Markdown( $text );
	}
}
