<?php
namespace Cy\SimpleDB\Handlers;

class OneFileJsonHandler implements \Cy\SimpleDB\PersistenceHandler
{
	private $path;
	private $data = null;
	private $loaded = false;
	private $fileExists = false;

	private function createFile() {
		if (!$this->fileExists) {
			if (!is_file($this->path)) {
				file_put_contents($this->path, '');
			}
		}
		$this->fileExists = true;
	}

	public function __construct($path)
	{
		$this->path = $path;
	}

	public function load()
	{
		if ($this->loaded) {
			return;
		}
		$this->loaded = true;
		$this->createFile();
		$this->data = json_decode(file_get_contents($this->path), true);
	}

	public function save()
	{
		file_put_contents($this->path, json_encode($this->data, JSON_PRETTY_PRINT));
	}

	public function loadForCollection($name)
	{
		$this->load();
                if (!isset($this->data[$name])) {
                    return NULL;
                }
		return $this->data[$name];
	}

	public function saveFromCollection($name, $data)
	{
		$this->load();
		$this->data[$name] = $data;
		$this->save();
	}

	public function hasKey($name) {
		return isset($this->data[$name]);
	}
}
