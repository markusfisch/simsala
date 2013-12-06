<?php

/**
 * Compose a web site from a tree of files
 */
class Simsala
{
	/** Relative path to content files */
	public $contentsDir = 'contents';

	/** Relative path to layout files; see layout() for details */
	public $layoutsDir = 'layouts';

	/** Relative output directory */
	public $htdocsDir = 'htdocs';

	/** Content file in process */
	private $file;

	/** Extension of output file */
	private $ext;

	/**
	 * Compose web site
	 */
	public function compose()
	{
		$this->process( $this->contentsDir );
	}

	/**
	 * Remove all output files
	 *
	 * @param $types - extensions of files to remove (optional)
	 */
	public function clean( $types = array( 'html', 'php' ) )
	{
		if( !($dh = opendir( $this->htdocsDir )) )
			return;

		while( ($name = readdir( $dh )) )
		{
			if( $name{0} == '.' )
				continue;

			$file = "{$this->htdocsDir}/{$name}";

			if( !is_dir( $file ) &&
				($p = strrpos( $file, '.' )) > -1 &&
				in_array( substr( $file, ++$p ), $types ) )
				unlink( $file );
		}

		closedir( $dh );
	}

	/**
	 * Process input files recursively
	 *
	 * @param $dir - directory
	 */
	protected function process( $dir )
	{
		if( !($dh = opendir( $dir )) )
			return;

		while( ($name = readdir( $dh )) )
		{
			if( $name{0} == '.' )
				continue;

			$file = "{$dir}/{$name}";

			if( is_dir( $file ) )
			{
				$this->process( $file );
				continue;
			}

			$this->file = $file;

			if( !($layout = $this->layout( $file )) ||
				!($content = $this->transform(
					file_get_contents( $file ) )) ||
				!ob_start() )
				break;

			if( ($p = strrpos( $layout, '.' )) > -1 )
				$this->ext = substr( $layout, $p );
			else
				$this->ext = '.html';

			$query = $this->query( $file );
			$title = $this->label( $name );

			eval( "?>\n" . file_get_contents( $layout ) );

			file_put_contents(
				"{$this->htdocsDir}/{$query}",
				ob_get_clean() );
		}

		closedir( $dh );
	}

	/**
	 * Transform input text; to use Markdown overwrite this method,
	 * see http://michelf.ca/projects/php-markdown/
	 *
	 * @param $text - text to transform
	 */
	protected function transform( $text )
	{
		return $text;
	}

	/**
	 * Return label for file
	 *
	 * @param $file - file or directory
	 */
	protected function label( $file )
	{
		return preg_replace(
			'/[-_]/',
			' ',
			basename( $file ) );
	}

	/**
	 * Return query string for file
	 *
	 * @param $file - file or directory
	 */
	protected function query( $file )
	{
		return strtolower( preg_replace(
			'.[-/].',
			'-',
			$this->relativeUrl( $file ) ) ) . $this->ext;
	}

	/**
	 * Return URL relative to content directory
	 *
	 * @param $file - file or directory
	 */
	protected function relativeUrl( $file )
	{
		$skip = strlen( $this->contentsDir );

		if( substr( $this->contentsDir, -1, 1 ) != '/' )
			++$skip;

		return substr( $file, $skip );
	}

	/**
	 * Determine layout from path and file name in reverse order
	 *
	 * @param $file - content file
	 */
	protected function layout( $file )
	{
		$parts = explode( '/', $file );

		for( $n = count( $parts ); $n--; )
		{
			$layout = "{$this->layoutsDir}/{$parts[$n]}.html";

			if( file_exists( $layout ) )
				return $layout;
		}

		return null;
	}

	/**
	 * Generate navigation tree from directory tree
	 *
	 * @param $dir - directory (optional)
	 */
	protected function nav( $dir = null )
	{
		if( !$dir )
			$dir = $this->contentsDir;

		$nav = '<ul class="NavList">';

		if( ($fp = @fopen( "{$dir}/.nav", 'r' )) )
		{
			while( ($name = fgets( $fp )) )
				$nav .= $this->navItem(
					$dir,
					strtok( $name, "\r\n" ) );

			fclose( $fp );
			$nav .= '</ul>';

			return $nav;
		}

		if( !($dh = opendir( $dir )) )
			return '';

		while( ($name = readdir( $dh )) )
		{
			if( $name{0} == '.' )
				continue;

			$nav .= $this->navItem( $dir, $name );
		}

		closedir( $dh );
		$nav .= '</ul>';

		return $nav;
	}

	/**
	 * Return navigation item
	 *
	 * @param $dir - directory
	 * @param $name - file name
	 */
	protected function navItem( $dir, $name )
	{
		if( !$name )
			return null;

		$file = "{$dir}/{$name}";
		$label = $this->label( $name );
		$classes = $label;
		$inPath = false;

		if( $file == $this->file ||
			!strncmp( $this->file, $file, strlen( $file ) ) )
		{
			$inPath = true;
			$classes .= ' Active';
		}

		$navItem =
			'<li class="NavListItem ' . $classes . '">' .
			'<a class="NavLink ' . $classes;

		if( is_dir( $file ) )
		{
			if( !($first = $this->firstNavItem( $file )) )
				return null;

			if( $inPath )
				return
					$navItem .
					'">' .
					$label . '</a>' .
					$this->nav( $file ) .
					'</li>';

			return
				$navItem .
				'" href="' . $first . '">' .
				$label . '</a></li>';
		}

		return
			$navItem .
			'" href="' . $this->query( $file ) . '">' .
			$label . '</a></li>';
	}

	/**
	 * Return query URL of first navigation item of a directory
	 *
	 * @param $dir - directory
	 */
	protected function firstNavItem( $dir )
	{
		$query = null;

		if( ($fp = @fopen( "{$dir}/.nav", 'r' )) )
		{
			if( ($name = fgets( $fp )) )
				$query = $this->query( "{$dir}/{$name}" );

			fclose( $fp );
		}
		else if( ($dh = opendir( $dir )) )
		{
			while( ($name = readdir( $dh )) )
			{
				if( $name{0} == '.' )
					continue;

				$file = "{$dir}/{$name}";

				if( is_dir( $file ) )
					continue;

				$query = $this->query( $file );

				break;
			}

			closedir( $dh );
		}

		return $query;
	}
}
