<?php
namespace Sigh\SimpleDB\Handlers;

class OneFileJsonHandler implements \Sigh\SimpleDB\PersistenceHandler
{
    private $path;
    private $data = null;
    private $loaded = false;
    private $fileExists = false;
    private $prettyPrint = false;

    private function createFile() {
        $dirpath = dirname($this->path);
        if (!is_dir($dirpath)) {
            if (!mkdir($dirpath, 0777, true)) {
                throw new Exception("Can not create directory to database file: " . $this->path);
            }
        }
        if (!$this->fileExists) {
            if (!is_file($this->path)) {
                file_put_contents($this->path, '');
            }
        }
        $this->fileExists = true;
    }

    public function __construct($path, $prettyPrint = false)
    {
        $this->path = $path;
        $this->prettyPrint = $prettyPrint;
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
        file_put_contents($this->path, json_encode($this->data, $this->prettyPrint && PHP_VERSION_ID > 50400 ? JSON_PRETTY_PRINT : 0));
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
