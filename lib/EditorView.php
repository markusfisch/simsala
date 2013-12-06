<?php

/**
 * Editor view
 */
class EditorView extends PhappView
{
	/** File manager */
	protected $fm;

	/** File or directory to edit */
	protected $edit;

	/** Current working directory (optional) */
	protected $cwd;

	/**
	 * Process requests
	 */
	public function request()
	{
		$this->fm = new FileManager(
			$this->app->simsala->contentsDir );

		$edit = $this->fm->safePath( $_REQUEST['edit'] );
		$cwd = $this->fm->safePath( $_REQUEST['cwd'] );

		if( ($name = $_REQUEST['name']) &&
			($name = $this->fileName( basename( $name ) )) )
		{
			if( $_REQUEST['mkdir'] )
			{
				$edit = "$cwd/$name";

				if( $this->fm->makeDirectory( $edit ) )
					NavigationFile::add( $edit );
			}
			else if( $_REQUEST['save'] ||
				($publish = $_REQUEST['publish']) )
			{
				$newName = $this->fm->safePath(
					($edit ?  dirname( $edit ) : $cwd) .
					"/$name" );

				if( is_dir( $edit ) )
				{
					if( $this->fm->move( $edit, $newName ) )
						NavigationFile::rename( $edit, $name );
				}
				else
					$this->saveFileAs(
						$edit,
						$newName,
						$_REQUEST['text'] );

				$edit = $newName;

				if( $publish )
				{
					$this->app->simsala->clean();
					$this->app->simsala->compose();
				}
			}
		}
		else if( ($file = $this->fm->safePath( $_REQUEST['remove'] )) )
		{
			if( $this->fm->remove( $file ) )
				NavigationFile::remove( $file );

			$cwd = dirname( $file );
		}
		else if(
			($file = $_REQUEST['up']) ||
			($file = $_REQUEST['down']) )
		{
			NavigationFile::move(
				($edit = $this->fm->safePath( $file )),
				$_REQUEST['up'] ? true : false );
		}
		else if( ($file = $this->fm->safePath( $_REQUEST['show'] )) )
		{
			NavigationFile::add( ($edit = $file) );
		}
		else if( ($file = $this->fm->safePath( $_REQUEST['hide'] )) )
		{
			NavigationFile::hide( ($edit = $file) );
		}

		if( $edit )
		{
			$this->edit = file_exists( $edit ) ? $edit : null;
			$this->cwd = dirname( $edit );
		}
		else if( $cwd )
			$this->cwd = $cwd;
		else
			$this->cwd = $this->app->simsala->contentsDir;

		return null;
	}

	/**
	 * Generate HTML response
	 */
	public function response()
	{
		if( file_exists( $this->edit ) )
			$actions = <<<EOF
<input type="hidden" name="edit" value="{$this->edit}"/>
EOF;
		else
			$actions = <<<EOF
<input type="hidden" name="cwd" value="{$this->cwd}"/>\n
EOF;

		if( is_file( $this->edit ) )
			$actions .= <<<EOF
<a href="#Rename">Rename</a>
<a href="?cwd={$this->cwd}#Name">New</a>\n
EOF;

		if( !is_dir( $this->edit ) )
			$editor = <<<EOF
<div class="Controls">
<input type="submit" name="save" value="Save"/>
<input type="submit" name="publish" value="Publish"/>
<a href="#Path">Path</a>
{$actions}</div>
<a name="Edit"></a>
<textarea class="Editor" name="text" rows="10"
placeholder="Enter text here">{$this->fm->getFile( $this->edit )}</textarea>\n
EOF;
		else
			$editor = $actions;

		return <<<EOF
<form action="?" method="post">
<a name="Path"></a>
<div class="Path">{$this->path( $this->cwd )}</div>
{$this->fileList( $this->cwd, $this->edit )}
{$editor}</form>
EOF;
	}

	/**
	 * Return linked path
	 *
	 * @param $dir - path to a directory
	 */
	protected function path( $dir )
	{
		$out = null;
		$path = '?cwd=';

		foreach( explode( '/', trim( $dir, '/' ) ) as $name )
		{
			if( $name == $this->app->simsala->contentsDir )
				$label = 'Home';
			else
				$label = $name;

			$path .= $name;

			$out .= <<<EOF
<a href="$path">$label</a><span class="Screenreader">/</span>\n
EOF;

			$path .= '/';
		}

		return $out;
	}

	/**
	 * Return file list
	 *
	 * @param $dir - root directory
	 * @param $selected - selected path and file name (optional)
	 */
	protected function fileList(
		$dir,
		$selected = null )
	{
		$out = '<ul class="Directory">';
		$nav = array();

		if( ($fp = @fopen(
			NavigationFile::navigationFileForDirectory( $dir ),
			'r' )) )
		{
			$clean = array();

			while( ($name = fgets( $fp )) )
			{
				if( !($name = strtok( $name, "\r\n" )) )
					continue;

				$file = "{$dir}/{$name}";

				if( !file_exists( $file ) )
				{
					$clean[] = $file;
					continue;
				}

				$nav[] = $name;
				$out .= $this->fileListItem(
					$dir,
					$name,
					$selected,
					false );
			}

			fclose( $fp );

			foreach( $clean as $file )
				NavigationFile::remove( $file );
		}

		if( ($dh = opendir( $dir )) )
		{
			$hidden = count( $nav ) > 0 ? true : false;

			while( ($name = readdir( $dh )) )
			{
				if( $name{0} == '.' ||
					in_array( $name, $nav ) )
					continue;

				$out .= $this->fileListItem(
					$dir,
					$name,
					$selected,
					$hidden );
			}

			closedir( $dh );
		}

		if( !$selected )
			$out .= <<<EOF
<li class="SelectedFile"><a name="Name"></a>
<input type="text" class="File" name="name" placeholder="Enter name"/>
<ul class="Actions">
<li><input type="submit" class="Action" name="mkdir" value="mkdir"/></li>
<li><input type="submit" class="Action" name="save" value="Save"/></li>
</ul></li>\n
EOF;

		$out .= '</ul>';

		return $out;
	}

	/**
	 * Return file list item
	 *
	 * @param $dir - directory
	 * @param $name - file name
	 * @param $selected - selected path and file name (optional)
	 * @param $hidden - hidden in navigation (optional)
	 */
	protected function fileListItem(
		$dir,
		$name,
		$selected = null,
		$hidden = false )
	{
		$file = "{$dir}/{$name}";
		$out = null;
		$classes = array();

		if( $hidden )
			$classes[] = 'Hidden';

		if( $selected == $file )
		{
			$classes[] = 'SelectedFile';
			$classes = implode( ' ', $classes );
			$time = time();

			$out .= <<<EOF
<li class="{$classes}"><a name="Rename"></a>
<input type="text" class="File" name="name" value="{$name}"/>
<ul class="Actions">
<li><input type="submit" class="Action" name="save" value="Save"/></li>
<li><a href="?up=${file}&amp;time=${time}#Rename" class="Action">Up</a></li>
<li><a href="?down=${file}&amp;time=${time}#Rename" class="Action">Down</a></li>\n
EOF;

			if( $hidden )
				$out .= <<<EOF
<li><a href="?show={$file}#Rename" class="Action">Show</a></li>\n
EOF;
			else
				$out .= <<<EOF
<li><a href="?hide={$file}#Rename" class="Action">Hide</a></li>\n
EOF;

			if( is_file( $file ) )
				$out .= <<<EOF
<li><a href="#Edit" class="Action">Edit</a></li>\n
EOF;
			else if( is_dir( $file ) )
				$out .= <<<EOF
<li><a href="?cwd={$file}" class="Action">Enter</a></li>\n
EOF;

			$out .= <<<EOF
<li><a href="?remove={$file}" class="Action">Delete</a></li>
</ul></li>\n
EOF;
		}
		else
		{
			if( is_dir( $file ) )
			{
				$action = 'cwd';
				$name .= '/';
			}
			else
			{
				$action = 'edit';
				$anchor = '#Edit';
			}

			if( $hidden )
				$name = <<<EOF
<span class="Screenreader">(</span>$name<span class="Screenreader">)</span>
EOF;

			if( ($classes = implode( ' ', $classes )) )
				$classes = ' class="' . $classes . '"';

			$out .= <<<EOF
<li{$classes}><a class="File" href="?{$action}={$file}{$anchor}">{$name}</a>
<a class="Manage" href="?edit={$file}#Rename">Manage</a></li>\n
EOF;
		}

		return $out;
	}

	/**
	 * Return proper file name
	 *
	 * @param $file - file name
	 */
	protected function fileName( $file )
	{
		return str_replace(
			' ',
			'-',
			$file );
	}

	/**
	 * Save a file by a new name and remove the original file
	 *
	 * @param $file - path and file name or null if there is no original
	 * @param $new - new path and file name
	 * @param $text - contents to put into file
	 */
	protected function saveFileAs( $file, $new, $text )
	{
		$text = str_replace(
			"\r",
			null,
			stripslashes( $text ) );

		$exists = file_exists( $file );

		if( !$this->fm->putFile( $new, $text ) )
			return false;

		if( $exists )
		{
			if( $file != $new )
			{
				unlink( $file );
				NavigationFile::rename(
					$file,
					basename( $new ) );
			}
		}
		else
			NavigationFile::add( $new );

		return true;
	}
}
