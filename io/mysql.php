<?php
    /* PDO mysql class
     *
     * autho: awen
     * date: 2014-02-27
     */
    class IoMysql extends _CoreIo{
        private $host ;
        private $port ;
        private $user ;
        private $passwd;

        private $conn;

        public function __construct($params) {
            $this->host = @$params[0];
            $this->port = @$params[1];
            $this->user = @$params[2];
            $this->passwd = @$params[3];
        }

        /*
        public function read("select * from $db.$table where id=$id"){}
        */

        /* get the array's most deepest dimention
         * 
         *@param $arr   
         *@return $dimention, int , if $arr is empty, $dimention = 0;
         */
        private function _arr_dimention(array $arr){
           $dimention = 0;
           foreach ($arr as $k => $v) {
               if (!is_array($v) && $dimention == 0) {
                   $dimention = 1;
                   
               } else if (is_array($v)) {
                   //step into next level
                   $sub_depth = 1 + $this->_arr_dimention($v);
                   $dimention = ($sub_depth > $dimention) ? $sub_depth : $dimention;
               }
           }
           return $dimention ;
        }

        private function _result($st, $type){
            switch ($type) {
                case "read":
                    return $st->fetchAll();

                case "write":
                    return $st->rowCount();

                default :
                    self::assert(false, CORE_ERR_IO, "unknown mysql io type", "type:".$type);
            }
        }

        /* mysql execute query
         *
         *@param    pdo_query,  mix,  
         *          1. pdo_query=string,  it will be execute as usuall 
         *          2. pdo_query=array( "query"=>query_string, "columns"=>array( ?,?,...) ),  it contents of one execute instruction
         *          3. pdo_query=array( "query"=>query_string, "columns"=>array( array(?,?,...), array(?,?,...) ), it contents of several execute instructions 
         *@param    type, string,  "read"(select statement)  or "write" (update statement)
         *@return   array( no => datas )
         */
        private function _execute($pdo_query, $type){
            // simple query string 
            if (is_string($pdo_query)) {
                $query = $pdo_query;
                $st = $this->conn->prepare($query);
                $res = $st->execute();

                self::assert($st->execute() === true, CORE_ERR_IO, "failed to execute query","query:".$query);

                return $this->_result($st, $type);

            } else {

                self::assert(@$pdo_query['query'] != null, CORE_ERR_IO, "empty query pdo query string ");
                self::assert(is_array(@$pdo_query['columns']), CORE_ERR_IO, "wrong columns for pdo");

                $query = $pdo_query['query'];
                
                // prepared query string
                $st = $this->conn->prepare($query);
                if ($this->_arr_dimention($pdo_query['columns']) == 1) {

                    self::assert($st->execute($pdo_query["columns"]) === true, CORE_ERR_IO, "failed to execute query", "query:".$query);

                    return $this->_result($st, $type);

                } else if ($this->_arr_dimention($pdo_query["columns"]) == 2) {
                    $res = array();
                    foreach ($pdo_query["columns"] as $i => $col) {

                        self::assert($st->execute($col) === true, CORE_ERR_IO, "failed to execute query,query:".$query);

                        $res[$i] = $this->_result($st, $type);
                    }
                    return $res;
                }
            }


        }

        public function read($pdo_query=null){
            $this->conn = new PDO("mysql:host=".$this->host.";port=".$this->port, $this->user, $this->passwd);
            return $this->_execute($pdo_query, "read");
        }

        public function write($pdo_query){
            $this->conn = new PDO("mysql:host=".$this->host.";port=".$this->port, $this->user, $this->passwd);
            return $this->_execute($pdo_query, "write");

        }

        public function flush_normally(){}
    }

/*


    class IoMemcached extends _CoreIo{
        public function __construct($params) {
            $this->host = @$params[0];
            $this->port = @$params[1];
        }
        public function read("get($id)"){}
    }


    class IoRedis extends _CoreIo{
        public function __construct($params) {
            $this->host = @$params[0];
            $this->port = @$params[1];
        }
        public function read("get($id)"){}
    }

    class IoMpi extends _CoreIo{
        const STOR_TYPE_MYSQL = 1; 
        const STOR_TYPE_REDIS = 2;

        private $storage;
        private $storage_type;

        private $op_mysql_db;
        private $op_mysql_table;

        public function __construct($params){
            $this->storage = $params[0];  //mysql or redis
            $this->storage_type = $params[1];  //storage type

            $this->table_router = $params[2];//table_router
            $this->cache = $params[3];   //memcached
        }
        private function _read_storage($id) {
            if ($this->storage_type == self::STORE_TYPE_MYSQL) {
                return $this->storage->read("select * from ".$this->op_mysql_db.".".$this->op_mysql_table." where id=".$id.";");
            }else if($this->storage_type == self::STORE_TYPE_
        }

        public function read($id, $index){
            if (!$this->cache->read(array("k"=>$id, "op"=>"get"))) {
                return $this->storage($id);       
            }
        }
    }

    */

