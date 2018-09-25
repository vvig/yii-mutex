<?php
/**
 * @link http://resurtm.com/
 * @copyright © 2014 resurtm
 * @license http://opensource.org/licenses/BSD-3-Clause
 */

/**
 * @author resurtm <resurtm@gmail.com>
 * @since 0.1
 */
class EFileMutex extends EMutex
{
	/**
	 * @var string the directory to store mutex files. You may use path alias here.
	 * Defaults to the "mutex" subdirectory under the application runtime path.
	 */
	public $mutexPath;
	/**
	 * @var integer the permission to be set for newly created mutex files.
	 * This value will be used by PHP chmod() function. No umask will be applied.
	 * If not set, the permission will be determined by the current environment.
	 */
	public $fileMode;
	/**
	 * @var integer the permission to be set for newly created directories.
	 * This value will be used by PHP chmod() function. No umask will be applied.
	 * Defaults to 0775, meaning the directory is read-writable by owner and group,
	 * but read-only for other users.
	 */
	public $dirMode=0775;
	/**
	 * @var resource[] stores all opened lock files. Keys are lock names and values are file handles.
	 */
	private $_files=array();

	/**
	 * Initializes mutex component implementation dedicated for UNIX, GNU/Linux, Mac OS X, and other UNIX-like
	 * operating systems.
	 * @throws CException
	 */
	public function init()
	{
		parent::init();
		if(stripos(php_uname('s'),'win')===0)
		{
			throw new CException('EFileMutex does not have MS Windows operating system support.');
		}
		if($this->mutexPath===null)
		{
			$this->mutexPath=Yii::app()->getRuntimePath().'/mutex';
		}
		else
		{
			$this->mutexPath=Yii::getPathOfAlias($this->mutexPath);
		}
		if(!is_dir($this->mutexPath))
		{
			self::createDirectory($this->mutexPath,$this->dirMode,true);
		}
	}

	/**
	 * Acquires lock by given name.
	 * @param string $name of the lock to be acquired.
	 * @param integer $timeout to wait for lock to become released.
	 * @return boolean acquiring result.
	 */
	protected function acquireLock($name,$timeout=0)
	{
		$fileName=$this->mutexPath.'/'.md5($name).'.lock';
		$file=fopen($fileName,'w+');
		if($file===false)
		{
			return false;
		}
		if($this->fileMode!==null)
		{
			@chmod($fileName,$this->fileMode);
		}
		$waitTime=0;
		while(!flock($file,LOCK_EX|LOCK_NB))
		{
			$waitTime++;
			if($waitTime>$timeout)
			{
				fclose($file);
				return false;
			}
			sleep(1);
		}
		$this->_files[$name]=$file;
		return true;
	}

	/**
	 * Releases lock by given name.
	 * @param string $name of the lock to be released.
	 * @return boolean release result.
	 */
	protected function releaseLock($name)
	{
		if(!isset($this->_files[$name]) || !flock($this->_files[$name],LOCK_UN))
		{
			return false;
		}
		else
		{
			fclose($this->_files[$name]);
			unset($this->_files[$name]);
			return true;
		}
	}

	/**
	 * This method was backported from the Yii 1.1.15 development version in order to allow users
	 * to use this class with the Yii 1.1.14.
	 *
	 * Shared environment safe version of mkdir. Supports recursive creation.
	 * For avoidance of umask side-effects chmod is used.
	 *
	 * @param string $dst path to be created.
	 * @param integer $mode the permission to be set for newly created directories, if not set - 0777 will be used.
	 * @param boolean $recursive whether to create directory structure recursive if parent dirs do not exist.
	 * @return boolean result of mkdir.
	 * @see mkdir
	 */
	private static function createDirectory($dst,$mode=null,$recursive=false)
	{
		if($mode===null)
		{
			$mode=0777;
		}
		$prevDir=dirname($dst);
		if($recursive && !is_dir($dst) && !is_dir($prevDir))
		{
			self::createDirectory(dirname($dst),$mode,true);
		}
		$res=mkdir($dst, $mode);
		@chmod($dst,$mode);
		return $res;
	}
}
