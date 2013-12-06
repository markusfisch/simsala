<?php

/**
 * Application template
 */
class Phapp
{
	private $views = array();

	/**
	 * Process view object
	 *
	 * @param $name - name of view object
	 * @return String html snippet
	 */
	public function process( $name )
	{
		do
		{
			$c = $this->get( $name );
		} while( ($name = $c->request()) );

		return $c->response();
	}

	/**
	 * Return requested view object
	 *
	 * @param $name - name of view object
	 * @return Object view object
	 */
	public function get( $name )
	{
		if( array_key_exists( $name, $this->views ) )
			return $this->views[$name];

		return ($this->views[$name] = new $name( $this ));
	}
}

/**
 * View template
 */
class PhappView
{
	protected $app = null;

	/**
	 * Constructor
	 *
	 * @parent $app - application object
	 */
	public function __construct( $app = null )
	{
		$this->app = $app;
	}

	/**
	 * Handle current request
	 *
	 * @return String name of object to take over or null if this object
	 *         shall respond to this request
	 */
	public function request()
	{
		return null;
	}

	/**
	 * Return response to current request
	 *
	 * @return String html snippet
	 */
	public function response()
	{
		return null;
	}
}
