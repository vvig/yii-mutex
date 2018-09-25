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
class EMysqlMutex extends EDbMutex
{
	/**
	 * @throws CException
	 * @inheritdoc
	 */
	public function init()
	{
		parent::init();
		if ($this->getDb()->getDriverName()!=='mysql')
		{
			throw new CException('In order to use EMysqlMutex class, connection must be configured to use MySQL database.');
		}
	}

	/**
	 * @inheritdoc
	 */
	protected function acquireLock($name, $timeout=0)
	{
		return (boolean)$this->getDb()
			->createCommand('SELECT GET_LOCK(:name, :timeout)')
			->queryScalar(array(':name'=>$name,':timeout'=>$timeout));
	}

	/**
	 * @inheritdoc
	 */
	protected function releaseLock($name)
	{
		return (boolean)$this->getDb()
			->createCommand('SELECT RELEASE_LOCK(:name)')
			->queryScalar(array(':name'=>$name));
	}
}
