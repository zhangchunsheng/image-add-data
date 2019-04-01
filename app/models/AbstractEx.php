<?php

use LM\Db\ManagerEx;

/**
 * 按照分表的方式扩展数据库
 *
 *
 *
 */
class AbstractExModel {

    const ST_OK = 1;
    const ST_DELETE = -1;

    protected $_table;
    protected $_primary;
    protected $_database;
    protected $_farmCount = 0;
    protected $_lastInstance = null;

    public function __construct() {
        if ($this->_table === null || $this->_primary === null || $this->_database === null) {
            throw new \Exception(__CLASS__ . ": table name or primary key or database name cannot be empty");
        }
        $this->_primary = is_array($this->_primary) ? $this->_primary : array($this->_primary);
    }

    /**
     * 根据 Farm ID 获取读数据库的实例, 为了简便，采用 VIP 的方式来实现负载均衡
     *
     * @param int $farm
     *
     * @return \LM\Db
     */
    protected function _getReader($farm = null) {
        return $this->_lastInstance = ManagerEx::getReader($this->_database, $farm);
    }

    /**
     * 根据 Farm ID 获取写数据库的实例
     * @param int $farm
     * 
     * @return \LM\Db
     */
    protected function _getWriter($farm = null) {
        return $this->_lastInstance = ManagerEx::getWriter($this->_database, $farm);
    }

    /**
     * 根据给定 ID 后三位来来获取对应的 Farm ID
     *
     * @param mixed $id
     *
     * @return int
     */
    protected function _farm($id) {
        $id = is_array($id) ? array_shift($id) : $id;
        $id = substr($id, -3);
        return $this->_farmCount === 0 ? null : ($id % $this->_farmCount) + 1;
    }

    /**
     * 根据 Farm ID 获取表名
     *
     * @param string $format
     * @param int    $farm
     *
     * @return string
     */
    protected function _name($format, $farm) {
        return $format ? sprintf($this->_table, $format, $farm) : $this->_table;
    }

    /**
     * 根据主键 ID 来获取记录
     *
     * @param mixed $id  user_id or cellphone or primary key
     *
     * @return array
     * */
    public function find($id) {
        $ids = is_array($id) ? $id : array($id);
        if (count($ids) != count($this->_primary)) {
            throw new \LM\Db\Exception("values count and primary key count not match for {$this->_table}");
        }
        $where = array();
        foreach ($ids as $key => $val) {
            $where[$this->_primary[$key]] = $val;
        }
        $farm = $this->_farm($id);
        $reader = $this->_getReader($farm);
        return $reader->get($this->_name($reader->getFormat(), $farm), '*', array("AND" => $where));
    }

    /**
     * 查找某一条记录
     *
     * @param array $where
     * @param mixed $fields
     * @param mixed $join
     * @param mixed $id     user_id or cellphone or primary key
     *
     * @return array
     */
    public function fetchRow($where, $fields = '*', $join = null, $id = null) {
        $farm = $this->_farm($id);
        $reader = $this->_getReader($farm);
        $table = $this->_name($reader->getFormat(), $this->_table);
        return $reader->get($table, $fields, $where);
    }

    /**
     * 查找多条记录
     *
     * @param array $where
     * @param mixed $fields
     * @param mixed $join
     * @param mixed $id     user_id or cellphone or primary key
     *
     * @return array
     */
    public function fetchAll($where, $fields = '*', $id = null) {
        $farm = $this->_farm($id);
        $reader = $this->_getReader($farm);
        $table = $this->_name($reader->getFormat(), $this->_table);
        return $reader->select($table, $fields, $where) ? : array();
    }

    public function foundRows() {
        return $this->_lastInstance ? $this->_lastInstance->found_rows() : 0;
    }

    public function insert($data, $id = null) {
        $farm = $this->_farm($id);
        $writer = $this->_getWriter($farm);
        $table = $this->_name($writer->getFormat(), $this->_table);
        return $writer->insert($table, $data);
    }

    public function batchInsert($datas, $id = null) {
        $farm = $this->_farm($id);
        $writer = $this->_getWriter($farm);
        $table = $this->_name($writer->getFormat(), $this->_table);
        return $writer->batch_insert($table, $datas);
    }

    public function update($data, $where, $id = null) {
        $farm = $this->_farm($id);
        $writer = $this->_getWriter($farm);
        $table = $this->_name($writer->getFormat(), $this->_table);
        return $writer->update($table, $data, $where);
    }

    public function delete($where, $id = null) {
        $farm = $this->_farm($id);
        $writer = $this->_getWriter($farm);
        $table = $this->_name($writer->getFormat(), $this->_table);
        return $writer->delete($table, $where);
    }

    public function __call($method, $args) {
        $methods = array("avg", "min", "count", "max", "sum");
        if (in_array($method, $methods)) {
            array_unshift($args, $this->_table);
            $obj = $this->_getReader($this->_farm(null));
            return call_user_func_array(array($obj, $method), $args);
        }
        throw new Exception(get_called_class() . " has no method $method");
    }

    public function __destruct() {
        
    }

}
