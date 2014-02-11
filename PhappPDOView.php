<?php

/**
 * Phapp view with data base access
 */
class PhappPDOView extends PhappView
{
	/**
	 * Begin transaction
	 */
	public function begin()
	{
		return $this->connect() ?
			$this->app->db->beginTransaction() :
			false;
	}

	/**
	 * Commit a transaction
	 */
	public function commit()
	{
		return $this->connect() ?
			$this->app->db->commit() :
			false;
	}

	/**
	 * Roll back a transaction
	 */
	public function rollback()
	{
		return $this->connect() ?
			$this->app->db->rollBack() :
			false;
	}

	/**
	 * Query database
	 *
	 * @param $q - SQL query
	 * @param ... - arguments
	 */
	public function query( $q )
	{
		if( !$this->connect() )
			return null;

		$a = func_get_args();
		array_shift( $a );

		$s = $this->app->db->prepare( $q );

		return $s->execute( $a ) ? $s : null;
	}

	/**
	 * Connect to database or just return true if already connected
	 */
	public function connect()
	{
		try
		{
			if( $this->app->db ||
				($this->app->db = new PDO(
					PDO_DSN,
					PDO_USER,
					PDO_PASS )) )
				return true;
		}
		catch( PDOException $e )
		{
			exit( 'ERROR: ' . $e->getMessage() . '<br/>' );
		}

		return false;
	}
}
