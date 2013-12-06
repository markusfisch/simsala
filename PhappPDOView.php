<?php

/**
 * Phapp view with data base access
 */
class PhappPDOView extends PhappView
{
	/**
	 * Query database
	 *
	 * @param $q - SQL query
	 * @param ... - arguments
	 */
	public function query( $q )
	{
		try
		{
			if( !$this->app->db &&
				!($this->app->db = new PDO(
					PDO_DSN,
					PDO_USER,
					PDO_PASS )) )
				return null;
		}
		catch( PDOException $e )
		{
			exit( 'ERROR: ' . $e->getMessage() . '<br/>' );
		}

		$a = func_get_args();
		array_shift( $a );

		$s = $this->app->db->prepare( $q );

		return $s->execute( $a ) ? $s : null;
	}
}
