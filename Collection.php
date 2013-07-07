<?php
namespace Sigh\SimpleDB;

class Collection implements \ArrayAccess
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
            $data = array(
                'lastId' => 0,
                'lastUpdate' => date('m/d/Y H:i:s'),
                'values' => array()
            );
        }
        return $data;
    }

    public function getLastUpdate()
    {
        $data = $this->getData();
        return $data['lastUpdate'];
    }
    
    public function select($predicate = null) 
    {
        if (is_callable($predicate)) {
            return $this->selectByPredicate($predicate);
        } elseif ($predicate !== NULL) {
            return array($this->selectOneById($predicate));
        } else {
            return $this->selectAll();
        }
    }
    public function selectOne($predicate = null) 
    {
        if (is_callable($predicate)) {
            return $this->selectOneByPredicate($predicate);
        } elseif ($predicate !== NULL) {
            return $this->selectOneById($predicate);
        }
        return NULL;
    }
    
    public function selectAll()
    {
        $data = $this->getData();
        $res = array();
        foreach ($data['values'] as $k => $v) {
            $res[] = $v;
        }
        return $res;
    }
    
    public function selectByPredicate($predicate)
    {
        $data = $this->getData();
        $res = array();
        foreach ($data['values'] as $k => $v) {
            if ($predicate($v)) {
                $res[] = $v;
            }
        }
        return $res;
    }
    
    public function selectOneByPredicate($predicate)
    {
        $data = $this->getData();
        foreach ($data['values'] as $k => $v) {
            if ($predicate($v)) {
                return $v;
            }
        }
        return NULL;
    }
    
    public function selectOneById($id)
    {
        $data = $this->getData();
        foreach ($data['values'] as $k => $v) {
            if ($v['id'] == $id) {
                return $v;
            }
        }
        return NULL;
    }

    public function insert($hash) 
    {
        $data = $this->getData();
        $data['lastUpdate'] = date('m/d/Y H:i:s');
        $data['lastId']++;
        $hash['id'] = $data['lastId'];
        $data['values'][] = $hash;
        $this->db->saveDataFromCollection($this->name, $data);
        return $hash;
    }
    
    public function update($hash, $predicate = NULL) 
    {
        $affected = 0;
        $data = $this->getData();
        foreach ($data['values'] as $k => $v) {
            if (is_callable($predicate)) {
                if ($predicate($v)) {
                    foreach ($hash as $k1 => $v1) {
                        $data['values'][$k][$k1] = $v1;
                    }
                    $affected++;
                }
            } elseif ($predicate !== NULL) {
                if ($v['id'] == $predicate) {
                    foreach ($hash as $k1 => $v1) {
                        $data['values'][$k][$k1] = $v1;
                    }
                    $affected++;
                }
            } else {
                foreach ($hash as $k1 => $v1) {
                    $data['values'][$k][$k1] = $v1;
                }
                $affected++;
            }
        }
        if ($affected > 0) {
            $data['lastUpdate'] = date('m/d/Y H:i:s');
        }
        $this->db->saveDataFromCollection($this->name, $data);
        return $affected;
    }
    
    public function merge($hash, $predicate = NULL) {
        $affected = $this->update($hash, $predicate);
        if (!$affected) {
            $this->insert($hash);
            $affected = 1;
        }
        return $affected;
    }

    public function delete($predicate) 
    {
        $affected = 0;
        $data = $this->getData();
        $to_delete = array();
        
        // Select items to delete
        foreach ($data['values'] as $k => $v) {
            if (is_callable($predicate)) {
                if ($predicate($v)) {
                    $to_delete[] = $k;
                }
            } elseif ($predicate !== NULL) {
                if ($predicate == $v['id']) {
                    $to_delete[] = $k;
                }
            } else {
                $to_delete[] = $k;
            }
        }
        
        // Unset items from array
        if (!empty($to_delete)) {
            foreach ($to_delete as $v) {
                unset($data['values'][$v]);
                $affected++;
            }
        }
        
        // Pack array to avoid fragmentation
        $tmp = array();
        foreach ($data['values'] as $v) {
            $tmp[] = $v;
        }
        
        if ($affected > 0) {
            $data['lastUpdate'] = date('m/d/Y H:i:s');
        }
        $data['values'] = $tmp;
        $this->db->saveDataFromCollection($this->name, $data);
        return $affected;
    }

    public function offsetExists($offset) {
        return $this->selectOne($offset) !== NULL;
    }

    public function offsetGet($offset) {
        return $this->selectOne($offset);
    }

    public function offsetSet($offset, $value) {
        return $this->update($value, $offset);
    }

    public function offsetUnset($offset) {
        return $this->delete($offset);
    }
}
