<?php

/**
 * Editor view
 */
class EditorView extends BaseView
{
	/** File manager */
	protected $fm;

	/** File or directory to edit */
	protected $edit;

	/** Current working directory if no file or directory to edit was set */
	protected $cwd;

	/**
	 * Process requests
	 */
	public function request()
	{
		if( ($view = parent::request()) )
			return $view;

		$this->fm = new FileManager(
			$this->app->simsala->contentsDir );

		if( ($edit = $_REQUEST['edit']) )
			$edit = $this->fm->local( $edit );
		else if( ($cwd = $_REQUEST['cwd']) );
			$cwd = $this->fm->local( $cwd );

		if( ($name = $_REQUEST['name']) &&
			($name = str_replace( ' ', '-', basename( $name ) )) )
		{
			if( $_REQUEST['mkdir'] )
			{
				$path = "$cwd/$name";

				if( $this->fm->makeDirectory( $path ) )
				{
					NavigationFile::add( $path );
					$edit = $path;
				}
			}
			else if( $_REQUEST['save'] ||
				($publish = $_REQUEST['publish']) )
			{
				$newName =
					($edit ? dirname( $edit ) : $cwd) .
					"/$name";

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
		else if( ($file = $this->fm->local( $_REQUEST['remove'] )) )
		{
			if( $this->fm->remove( $file ) )
				NavigationFile::remove( $file );

			$edit = null;
			$cwd = dirname( $file );
		}
		else if(
			($file = $_REQUEST['up']) ||
			($file = $_REQUEST['down']) )
		{
			$file = $this->fm->local( $file );

			NavigationFile::move(
				$file,
				$_REQUEST['up'] ? true : false );

			$edit = $file;
		}
		else if( ($file = $this->fm->local( $_REQUEST['show'] )) )
		{
			NavigationFile::add( $file );
			$edit = $file;
		}
		else if( ($file = $this->fm->local( $_REQUEST['hide'] )) )
		{
			NavigationFile::hide( $file );
			$edit = $file;
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
<a href="#Rename">{$this->tr( 'Rename' )}</a>
<a href="?cwd={$this->cwd}#Name">{$this->tr( 'New' )}</a>\n
EOF;

		if( !is_dir( $this->edit ) )
			$editor = <<<EOF
<div class="Controls">
<input type="submit" name="save" value="{$this->tr( 'Save' )}"/>
<input type="submit" name="publish" value="{$this->tr( 'Publish' )}"/>
<a href="#Path">{$this->tr( 'Path' )}</a>
{$actions}</div>
<a name="Edit"></a>
<textarea name="text" rows="10"
placeholder="{$this->tr( 'Enter text here' )}"
class="Editor">{$this->fm->read( $this->edit )}</textarea>\n
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
		$path = '?cwd=';
		$contents = null;

		foreach( explode( '/', trim( $dir, '/' ) ) as $name )
		{
			if( $name == $this->app->simsala->contentsDir )
				$label = $this->tr( 'Home' );
			else
				$label = $name;

			$path .= $name;

			$contents .= <<<EOF
<a href="$path">$label</a><span class="Screenreader">/</span>\n
EOF;

			$path .= '/';
		}

		return $contents;
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
		$contents = '<ul class="Directory">';
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
				$contents .= $this->fileListItem(
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

				$contents .= $this->fileListItem(
					$dir,
					$name,
					$selected,
					$hidden );
			}

			closedir( $dh );
		}

		if( !$selected )
			$contents .= <<<EOF
<li class="SelectedFile"><a name="Name"></a>
<input type="text" class="File" name="name"
placeholder="{$this->tr( 'Enter name' )}"/>
<ul class="Actions">
<li><input type="submit" class="Action" name="mkdir"
value="{$this->tr( 'mkdir' )}"/></li>
<li><input type="submit" class="Action" name="save"
value="{$this->tr( 'Save' )}"/></li>
</ul></li>\n
EOF;

		$contents .= '</ul>';

		return $contents;
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
		$contents = null;
		$classes = array();

		if( $hidden )
			$classes[] = 'Hidden';

		if( $selected == $file )
		{
			$classes[] = 'SelectedFile';
			$classes = implode( ' ', $classes );
			$time = time();

			$contents .= <<<EOF
<li class="{$classes}"><a name="Rename"></a>
<input type="text" class="File" name="name" value="{$name}"/>
<ul class="Actions">
<li><input type="submit" class="Action" name="save"
value="{$this->tr( 'Save' )}"/></li>
<li><a href="?up=${file}&amp;time=${time}#Rename"
class="Action">{$this->tr( 'Up' )}</a></li>
<li><a href="?down=${file}&amp;time=${time}#Rename"
class="Action">{$this->tr( 'Down' )}</a></li>\n
EOF;

			if( $hidden )
				$contents .= <<<EOF
<li><a href="?show={$file}#Rename"
class="Action">{$this->tr( 'Show' )}</a></li>\n
EOF;
			else
				$contents .= <<<EOF
<li><a href="?hide={$file}#Rename"
class="Action">{$this->tr( 'Hide' )}</a></li>\n
EOF;

			if( is_file( $file ) )
				$contents .= <<<EOF
<li><a href="#Edit" class="Action">{$this->tr( 'Edit' )}</a></li>\n
EOF;
			else if( is_dir( $file ) )
				$contents .= <<<EOF
<li><a href="?cwd={$file}" class="Action">{$this->tr( 'Enter' )}</a></li>\n
EOF;

			$contents .= <<<EOF
<li><a href="?remove={$file}"
onclick="return confirm( '{$this->tr( 'Are you sure?' )}' )"
class="Action">{$this->tr( 'Delete' )}</a></li>
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

			$contents .= <<<EOF
<li{$classes}><a class="File" href="?{$action}={$file}{$anchor}">{$name}</a>
<a href="?edit={$file}#Rename"
class="Manage">{$this->tr( 'Manage' )}</a></li>\n
EOF;
		}

		return $contents;
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

		if( !$this->fm->write( $new, $text ) )
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
