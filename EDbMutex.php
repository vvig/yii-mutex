<?php
/**
 * @link http://resurtm.com/
 * @copyright © 2015 resurtm
 * @license http://opensource.org/licenses/BSD-3-Clause
 */

/**
 * @author resurtm <resurtm@gmail.com>
 * @since 0.2
 */
abstract class EDbMutex extends EMutex
{
	/**
	 * @var string
	 */
	public $connectionID='db';
	/**
	 * @var CDbConnection
	 */
	private $_db;

	/**
	 * @return CDbConnection
	 * @throws CException
	 */
	public function getDb()
	{
		if($this->_db===null)
		{
			$this->_db=Yii::app()->getComponent($this->connectionID);
			if(!$this->_db instanceof CDbConnection)
			{
				throw new CException('EDbMutex.connectionID is invalid. Please make sure "' . $this->connectionID . '" refers to a valid database application component.');
			}
		}
		return $this->_db;
	}
}
