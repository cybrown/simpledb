<?php
namespace Cy\SimpleDB;

use ArrayAccess;

class Database implements ArrayAccess
{
	private $isOpen = false;
	private $handler = null;

	public function __construct(PersistenceHandler $handler)
	{
		$this->handler = $handler;
	}

	public function getDataForCollection($name)
	{
		if ($this->isOpen == false) {
			throw new SimpleDBException("Database must be open.");
		}
		return $this->handler->loadForCollection($name);
	}

	public function saveDataFromCollection($name, $data)
	{
		if ($this->isOpen == false) {
			throw new SimpleDBException("Database must be open.");
		}
		$this->handler->saveFromCollection($name, $data);
	}

	public function open()
	{
		if ($this->isOpen != false) {
			throw new SimpleDBException("Database must be closed.");
		}
		$this->handler->load();
		$this->isOpen = true;
	}

	public function close()
	{
		if ($this->isOpen == false) {
			throw new SimpleDBException("Database must be open.");
		}
		$this->isOpen = false;
		$this->handler->save();
	}

	public function offsetExists($offset)
	{
		if ($this->isOpen == false) {
			throw new SimpleDBException("Database must be open.");
		}
		return $this->handler->hasKey($offset);
	}

	public function offsetGet($offset)
	{
		if ($this->isOpen == false) {
			throw new SimpleDBException("Database must be open.");
		}
		return new Collection($this, $offset);
	}
	
	public function offsetSet($offset, $value)
	{
		throw new SimpleDBException("Operation not permited.");
	}
	
	public function offsetUnset($offset)
	{
		throw new SimpleDBException("Operation not permited.");
	}
}
