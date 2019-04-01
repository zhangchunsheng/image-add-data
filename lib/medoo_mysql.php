<?php

require __DIR__ . '/medoo.php';

class medoo_mysql extends medoo {

    protected $debug = FALSE;

    public function __construct( $options = NULL ) {
        $this->debug = isset( $options['debug'] ) ? $options['debug'] : FALSE;
        if ( is_array( $options ) ) {
            unset( $options['debug'] );
        }
        parent::__construct( $options );
    }
//    public function insert( $table, $datas ) {
//        foreach ( $datas as $key=>$data ) {
//            if($data===''){
//                unset($datas[$key]);
//            }
//        }
//
//        return parent::insert($table,$datas);
//    }

    protected function select_context(
        $table, $join, &$columns = NULL, $where = NULL, $column_fn = NULL
    ) {
        $foundRows = FALSE;
        if ( is_array( $where ) && isset( $where['FOUND_ROWS'] ) ) {
            $foundRows = $where['FOUND_ROWS'];
            unset( $where['FOUND_ROWS'] );
        }
        if ( is_array( $columns ) && isset( $columns['FOUND_ROWS'] ) ) {
            $foundRows = $columns['FOUND_ROWS'];
            unset( $columns['FOUND_ROWS'] );
        }
        $sql = parent::select_context( $table, $join, $columns, $where, $column_fn );

        return $foundRows ? ( "SELECT SQL_CALC_FOUND_ROWS " . substr( $sql, 7 ) ) : $sql;
    }


    public function found_rows() {
        $ret = $this->query( "SELECT FOUND_ROWS()" )->fetchColumn();

        return is_numeric( $ret ) ? $ret + 0 : 0;
    }

    private $last_query = [ ];



    public function query( $query ) {
        try {
            $t   = \LM\Timer::startNewTimer();
            $ret = $this->pdo->query( $query );
            $ms  = $t->getMs();
            $count = DEBUG == 1 ? $ret->rowCount() : - 1;
            if ( $ms > 200 ) {
                \LM\LoggerHelper::WARNING( 'DB_query_slow', $ms,
                    [ 'SQL' => $query, 'COUNT' => $count ] );
            }
            if ( DEBUG >= 1 ) {
                if ( strpos( $query, 'SQL_CALC_FOUND_ROWS' ) !== FALSE ) {
                    $this->last_query = [ $query, $ms, '', $count, ];
                } else {
                    $a = function()use($query,$ms,$count){$this->explain($query,$ms,'',$count);};
                    if ( $query == 'SELECT FOUND_ROWS()' ) {
                        call_user_func_array( [ $this, 'explain' ], $this->last_query );
                    }
                    isset($a)&& $a();
                }

            }

            return $ret;
        } catch ( \Exception $e ) {
            \LM\LoggerHelper::ERR( 'DB_error',
                [ 'SQL' => $query, 'EXCEPTION' => $e->getMessage() ] );
            throw $e;
        }
    }

    public function exec( $query ) {
        try {
            $t   = \LM\Timer::startNewTimer();
            $ret = $this->pdo->exec( $query );
            $ms  = $t->getMs();
            if ( $ms > 200 ) {
                \LM\LoggerHelper::WARNING( 'DB_query_slow', $ms, [ 'SQL' => $query ] );
            }
            if ( DEBUG >= 1 ) {
                $this->explain( $query, $ms, $ret );
            }
            return $ret;
        } catch ( \Exception $e ) {
            \LM\LoggerHelper::ERR( 'DB_error',
                [ 'SQL' => $query, 'EXCEPTION' => $e->getMessage() ] );
            throw $e;
        }
    }

    protected function explain( $query, $ms, $ret, $count = - 1 ) {
        $action =  strtok( $query, ' ' ) ;
        switch ( $action ) {
            case 'SELECT':
                $explainSql = $query;
                break;
            case 'UPDATE':
                $explainSql = str_replace( 'UPDATE', 'SELECT * FROM ',
                    preg_replace( '/SET.*WHERE/', 'WHERE', $query ) );
                break;
            case 'DELETE':
                $explainSql = str_replace( 'DELETE', 'SELECT * ',
                    $query );
                break;
            default:
                \LM\LoggerHelper::INFO('DB_sql',$ms, [
                    'SQL'     => $query,
                    'RET'     => $ret,
                ] );
                return;

        }
        $explain=[];
        if($query!='SELECT FOUND_ROWS()'){
            $explain = $this->pdo->query( 'EXPLAIN ' . $explainSql )->fetchAll( PDO::FETCH_ASSOC );
        }

        \LM\LoggerHelper::DEBUG( 'DB_sql', $ms,
            [ 'SQL' => $query, 'EXPLAIN_SQL' => $explainSql,'RET'=>$ret, 'COUNT' => $count, 'EXPLAIN' => $explain ] );
        if ( !empty($explain) && empty( $explain[0]['key'] ) && $explain[0]['rows'] > 50
             && $explain[0]['Extra'] != 'Impossible WHERE noticed after reading const tables'
        ) {
            \LM\LoggerHelper::ERR( 'DB_error', $ms, [
                    'SQL'     => $query,
                    'EXPLAIN_SQL' => $explainSql,
                    'COUNT'   => $count,
                    'RET'     => $ret,
                    'EXPLAIN' => $explain,
                ] );
            //throw new \Exception('SQL:'.$query.' ; NO KEY' );
        }
    }
    public function get_next_seq_id( $table, $field, $step = 1 ) {
        $step  = (int) $step;
        $field = $this->column_quote( $field );
        $this->exec( "UPDATE ".$this->table_quote($table)." set $field = last_insert_id($field + $step)" );

        return $this->pdo->lastInsertId();
    }

    public function begin() {
        $this->pdo->beginTransaction();
    }


    public function commit() {
        $this->pdo->commit();
    }

    public function rollback() {
        $this->pdo->rollback();
    }

    public function getPdo() {
        return $this->pdo;
    }

    public function __destruct() {
        if ( $this->pdo->inTransaction() ) {
            $this->pdo->rollback();
        }
        /*
          foreach($this->logs as $r) {
          error_log($r);
          }
         */
    }

}
