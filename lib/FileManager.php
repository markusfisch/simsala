<?php

/**
 * File Manager
 */
class FileManager
{
	/** Root directory */
	protected $root;

	/**
	 * Initialize file manager
	 *
	 * @param $root - root directory
	 */
	public function FileManager( $root )
	{
		$this->root = $root;
	}

	/**
	 * Return local path and file
	 *
	 * @param $file - path and file name
	 */
	public function local( $file )
	{
		if( strncmp( $file, $this->root, strlen( $this->root ) ) ||
			strpos( $file, '..' ) > -1 )
			return null;

		return $file;
	}

	/**
	 * Get contents from file
	 *
	 * @param $file - path and file name
	 */
	public function read( $file )
	{
		if( !($file = $this->local( $file )) ||
			!is_file( $file ) )
			return null;

		return file_get_contents( $file );
	}

	/**
	 * Put contents into file
	 *
	 * @param $file - path and file name or just the target directory
	 * @param $text - contents to put into file
	 */
	public function write( $file, $text )
	{
		return
			($file = $this->local( $file )) &&
			file_put_contents( $file, $text ) !== false;
	}

	/**
	 * Make a new directory
	 *
	 * @param $path - new directory
	 */
	public function makeDirectory( $path )
	{
		return
			($path = $this->local( $path )) &&
			mkdir( $path ) !== false;
	}

	/**
	 * Move a file or directory
	 *
	 * @param $old - old path
	 * @param $new - new path
	 */
	public function move( $old, $new )
	{
		if( !($old = $this->local( $old )) ||
			!($new = $this->local( $new )) )
			return false;

		return rename( $old, $new );
	}

	/**
	 * Remove file
	 *
	 * @param $file - path and file name
	 */
	public function remove( $file )
	{
		if( !($file = $this->local( $file )) )
			return false;

		if( is_dir( $file ) )
			return $this->removeDirectory( $file );

		if( !unlink( $file ) )
			return false;

		return true;
	}

	/**
	 * Recursively delete directory
	 *
	 * @param $dir - path and directory name
	 */
	public function removeDirectory( $dir )
	{
		if( !($dir = $this->local( $dir )) ||
			!($dh = opendir( $dir )) )
			return false;

		while( ($name = readdir( $dh )) )
		{
			if( $name == '.' ||
				$name == '..' )
				continue;

			$file = "{$dir}/{$name}";

			if( is_dir( $file ) )
				$r = $this->removeDirectory( $file );
			else
				$r = unlink( $file );

			if( $r === false )
				break;
		}

		closedir( $dh );
		rmdir( $dir );

		return $r;
	}
}
