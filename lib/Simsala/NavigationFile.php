<?php

/**
 * Manage navigation file
 */
class NavigationFile
{
	/**
	 * Add file to navigation
	 *
	 * @param $file - path and file
	 */
	static public function add( $file )
	{
		if( !($navFile = NavigationFile::navigationFileFor( $file )) )
			return false;

		// generate navigation file and make sure
		// the file to add is not already there
		NavigationFile::hide( $file );

		if( ($nav = file_get_contents( $navFile )) &&
			substr( $nav, -1 ) != "\n" )
			$nav .= "\n";

		return file_put_contents(
			$navFile,
			$nav . basename( $file ) );
	}

	/**
	 * Rename file in navigation
	 *
	 * @param $file - old path and file name
	 * @param $name - new file name
	 */
	static public function rename( $file, $name )
	{
		if( !($navFile = NavigationFile::navigationFileFor( $file )) ||
			!file_exists( $navFile ) )
			return false;

		return file_put_contents(
			$navFile,
			preg_replace(
				'/\b' . basename( $file ) . '\b/',
				$name,
				file_get_contents( $navFile ) ) );
	}

	/**
	 * Remove file from navigation
	 *
	 * @param $file - path and file
	 */
	static public function remove( $file )
	{
		if( !($navFile = NavigationFile::navigationFileFor( $file )) ||
			!file_exists( $navFile ) )
			return false;

		$name = basename( $file );

		return file_put_contents(
			$navFile,
			preg_replace(
				'/\b([\r\n]+' . $name . '|' .
					$name . '[\r\n]+)\b/',
				null,
				file_get_contents( $navFile ) ) );
	}

	/**
	 * Move file up or down in navigation
	 *
	 * @param $file - path and file
	 * @param $up - true for up, false for down (optional)
	 */
	static public function move( $file, $up = true )
	{
		if( !$file ||
			!($navFile = NavigationFile::navigationFileFor(
				$file )) )
			return false;

		if( file_exists( $navFile ) )
		{
			$list = explode(
				"\n",
				file_get_contents( $navFile ) );
		}
		else if( ($dh = opendir( dirname( $file ) )) )
		{
			$list = array();

			while( ($name = readdir( $dh )) )
			{
				if( $name{0} == '.' )
					continue;

				$list[] = $name;
			}

			closedir( $dh );
		}

		$name = basename( $file );
		$nav = null;
		$after = null;

		foreach( $list as $item )
		{
			if( !$item )
				continue;

			$item = trim( $item, "\r" );

			if( $item == $name )
			{
				if( $up )
				{
					if( $nav )
					{
						$before = trim( $nav, "\n" );

						if( ($p = strrpos(
							$before,
							"\n" )) > -1 )
						{
							$last = substr(
								$before,
								++$p );

							$before = substr(
								$before,
								0,
								$p );
						}
						else
						{
							$last = $before;
							$before = null;
						}

						$nav =
							$before .
							"{$name}\n" .
							"{$last}\n";

						continue;
					}
				}
				else
				{
					$after = $item;
					continue;
				}
			}

			$nav .= "{$item}\n";

			if( $after )
			{
				$nav .= "{$after}\n";
				$after = null;
			}
		}

		if( $after )
			$nav .= "{$after}\n";

		return file_put_contents( $navFile, $nav );
	}

	/**
	 * Hide file from navigation
	 *
	 * @param $file - path and file
	 */
	static public function hide( $file )
	{
		if( !$file )
			return false;

		$navFile = NavigationFile::navigationFileFor( $file );

		if( !file_exists( $navFile ) &&
			($dh = opendir( dirname( $file ) )) )
		{
			$hide = basename( $file );
			$nav = null;

			while( ($name = readdir( $dh )) )
			{
				if( $name{0} == '.' )
					continue;

				if( $name != $hide )
					$nav .= "{$name}\n";
			}

			closedir( $dh );

			return file_put_contents( $navFile, $nav );
		}

		return NavigationFile::remove( $file );
	}

	/**
	 * Return navigation file for a content file
	 *
	 * @param $file - path and file
	 */
	static public function navigationFileFor( $file )
	{
		return NavigationFile::navigationFileForDirectory(
			dirname( $file ) );
	}

	/**
	 * Return navigation file for directory
	 *
	 * @param $dir - directory
	 */
	static public function navigationFileForDirectory( $dir )
	{
		return "{$dir}/.nav";
	}
}
