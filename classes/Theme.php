<?php

class Theme
{
	/*** Attributes: ***/
	private $current_theme;

	public function Theme(){
		$this->current_theme = "default";
	}
	/**
	 * Recursive function to copy elements from a folder to other
	 *
	 * @param $src source file's path
	 * @param $dst destion of files
	 */
	private function recursive_copy($src,$dst) {
		$dir = opendir($src);
		@mkdir($dst);
		while(false !== ( $file = readdir($dir)) ) {
			if (( $file != '.' ) && ( $file != '..' ) && ($file != 'mini_thumbnail.png') && ($file != 'thumbnail.png')) {
				if ( is_dir($src . '/' . $file) ) {
					$this->recursive_copy($src . '/' . $file,$dst . '/' . $file);
				}
				else {
					copy($src . '/' . $file,$dst . '/' . $file);
				}
			}
		}
		closedir($dir);
	}
	/**
	 * Function to copy theme files to the application path
	 * @param $theme_name application's theme name
	 * @param $path application's main path
	 */
	public function copyFiles($path){
		$this->recursive_copy("themes/appgen/".$this->current_theme,$path);
	}
	/**
	 * Returns an array of detected themes
	 *
	 * @return string array of detected themes
	 * @access public
	 */
	public function getInstalledThemes( ) {
		$themes = array();
		$dir = dir("./themes/appgen");
		while($folder=$dir->read()) {
			if(($folder!='.')&&($folder!='..')&&($folder!='.svn'))
			$themes[] = $folder;
		}
		$dir->close();
		return $themes;
	}
	/**
	 * Returns the name of the current theme used by the application
	 *
	 * @return string with theme's name
	 * @access public
	 */
	public function getThemeName( ) {
		return $this->current_theme;
	}
	/**
	 * Sets the theme to be used by the application
	 *
	 * @param $name name of the theme
	 */
	public function setThemeName( $name ) {
		$this->current_theme =$name;
	}
} // end of Theme
?>
