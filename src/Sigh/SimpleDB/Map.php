<?php
namespace Sigh\SimpleDB;

class Map implements \ArrayAccess
{
    public $db;
    public $name;

    public function __construct($db, $name)
    {
        $this->db = $db;
        $this->name = $name;
    }

    private function getData() {
        $data = $this->db->getDataForCollection($this->name);
        if (!is_array($data)) {
            $data = array();
        }
        return $data;
    }

    public function offsetExists($offset) {
        $data = $this->getData();
        return isset($data[$offset]);
    }

    public function offsetGet($offset) {
        $data = $this->getData();
        return $data[$offset];
    }

    public function offsetSet($offset, $value) {
        $data = $this->getData();
        $data[$offset] = $hash;
        $this->db->saveDataFromCollection($this->name, $data);
    }

    public function offsetUnset($offset) {
        $data = $this->getData();
        unset($data[$offset]);
        $this->db->saveDataFromCollection($this->name, $data);
    }
}
