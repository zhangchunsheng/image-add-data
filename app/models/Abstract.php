<?php

use LM\Db\Manager;

/**
 * 按照分库的方式来扩展数据库
 *
 *
 */
class AbstractModel {
    const CACHE_KEY_PREFIX = 'SW:';
    const ST_OK = 1;
    const ST_FROZEN = 2;
    const ST_DELETE = -1;
    const ST_ALL = "-100"; //获取全部
    const DEFAULT_ORDER_ID = 9999;

    protected $_table;
    protected $_primary;
    protected $_database;
    protected $_farmCount = 0;
    protected $_lastInstance = null;

    protected static $_statusTextList = array(
        self::ST_OK => "正常",
        self::ST_FROZEN => "冻结",
        self::ST_DELETE => "删除"
    );

    public static function getInstance() {
        static $_instance = NULL;
        if (empty($_instance)) {
            $_instance = new static();
        }
        return $_instance;
    }

    public static function getStatusTextList() {
        return static::$_statusTextList;
    }

    public static function getStatusText($status = 0) {
        return isset(static::$_statusTextList[$status]) ? static::$_statusTextList[$status] : '-';
    }

    public static function issetFlag($flag, $bit) {
        return ($flag & $bit) == $bit;
    }

    public function __construct() {
        if ($this->_table === null || $this->_primary === null || $this->_database === null) {
            throw new \Exception(__CLASS__ . ": table name or primary key or database name cannot be empty");
        }
        $this->_primary = is_array($this->_primary) ? $this->_primary : array($this->_primary);
    }

    protected function _getReader($farm = null, $forceMaster = FALSE) {
        if ($forceMaster) {
            return $this->_getWriter($farm);
        }
        return $this->_lastInstance = Manager::getReader($this->_database, $farm);
    }

    protected function _getWriter($farm = null) {
        return $this->_lastInstance = Manager::getWriter($this->_database, $farm);
    }

    protected function _farm($id) {
        $id = is_array($id) ? array_shift($id) : $id;
        return $this->_farmCount === 0 ? null : ($id % $this->_farmCount) + 1;
    }

    /**
     *
     *
     *
     * */
    public function find($id, $forceMaster = false) {
        $ids = is_array($id) ? $id : array($id);
        if (count($ids) != count($this->_primary)) {
            throw new \LM\Db\Exception("values count and primary key count not match for {$this->_table}");
        }
        $where = array();
        foreach ($ids as $key => $val) {
            $where[$this->_primary[$key]] = $val;
        }
        return $this->_getReader($this->_farm($id), $forceMaster)->get($this->_table, '*', array("AND" => $where));
    }

    public function fetchRow($where, $fields = '*', $id = null, $forceMaster = false) {
        if (isset($where['LIMIT'])) {
            unset($where['LIMIT']);
        }
        return $this->_getReader($this->_farm($id), $forceMaster)->get($this->_table, $fields, $where);
    }

    public function fetchAll($where, $fields = '*', $id = null, $forceMaster = false) {
        unset($where['FOUND_ROWS']);
        return $this->_getReader($this->_farm($id), $forceMaster)->select($this->_table, $fields, $where) ? : array();
    }

    public function fetchAllAndCount($where, $fields = '*', $id = null, $forceMaster = false) {
        $rows = [];
        $count = -1;
        if (isset($where['FOUND_ROWS']) && $where['FOUND_ROWS'] == 1) {
            $where['FOUND_ROWS'] = 1;
            $rows = $this->_getReader($this->_farm($id), $forceMaster)->select($this->_table, $fields, $where) ? : array();
            $count = $this->foundRows();
        } else {
            $rows = $this->_getReader($this->_farm($id), $forceMaster)->select($this->_table, $fields, $where) ? : array();
        }
        return ["lists" => $rows, "count" => $count];
    }

    public function foundRows() {
        return $this->_lastInstance ? $this->_lastInstance->found_rows() : 0;
    }

    public function insert($data, $id = null) {
        return $this->_getWriter($this->_farm($id))->insert($this->_table, $data);
    }

    public function batchInsert($datas, $id = null) {
        return $this->_getWriter($this->_farm($id))->batch_insert($this->_table, $datas);
    }

    public function update($data, $where, $id = null) {
        return $this->_getWriter($this->_farm($id))->update($this->_table, $data, $where);
    }

    public function delete($where, $id = null) {
        return $this->_getWriter($this->_farm($id))->delete($this->_table, $where);
    }

    public function begin($id = null) {
        return $this->_getWriter($this->_farm($id))->begin();
    }

    public function commit($id = null) {
        return $this->_getWriter($this->_farm($id))->commit();
    }

    public function rollback($id = null) {
        return $this->_getWriter($this->_farm($id))->rollback();
    }

    /**
     * @param $sql
     * @param null $id
     * @return mixed
     */
    public function query($sql, $id = null) {
        return $this->_getWriter($this->_farm($id))->query($sql);
    }

    public function exec($sql, $id = null) {
        return $this->_getWriter($this->_farm($id))->exec($sql);
    }

    public function __call($method, $args) {
        if(strncmp($method, "getCached", 9) == 0) {
            $cache = new Yac(self::CACHE_KEY_PREFIX . get_called_class() . '::');
            $method = "get" . substr($method, 9);
            $key = $method  . ':' . join("_", $args);
            $ret = $cache->get($key);
            if($ret === false) {
                $ret = call_user_func_array(array($this, $method), $args);
                $cache->set($key, $ret, 600);
            }
            return $ret;
        }
        $methods = array("avg", "min", "count", "max", "sum");
        if (in_array($method, $methods)) {
            array_unshift($args, $this->_table);
            $obj = $this->_getReader($this->_farm(null));
            return call_user_func_array(array($obj, $method), $args);
        }
        throw new \Exception(get_called_class() . " has no method $method");
    }

    public function __destruct() {
        
    }
}
