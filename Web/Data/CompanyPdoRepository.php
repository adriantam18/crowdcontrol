<?php
    class CompanyPdoRepository implements CompanyRepository{
        private $pdo;

        function __construct($pdo){
            $this->pdo = $pdo;
        }

        /**
         *  A method responsible for preparing and executing SQL queries.
         *  @param string $query SQL query to be executed.
         *  @param array $args Associative array that contains the key/value mappings of named placeholders.
         *  @return PDOStatement Returns pdostatement associated with the result set of the executed query.
         */
        private function prepAndExecute($query, $args = array()){
            try{
                $stmt = $this->pdo->prepare($query);

                if(!empty($args)){
                    foreach ($args as $key => $value) {
                        $stmt->bindValue($key, $value);
                    }
                    $stmt->execute($args);
                }else{
                    $stmt->execute();
                }
                return $stmt;
            }catch(PDOException $exception){
                return null;
            }
        }

        /**
         *  A method responsible for SELECT queries.
         *  @param string $query SELECT sql query to be executed.
         *  @param array $args Associative array that contains the key/value mappings of named placeholders.
         *  @return array Returns an associative array containing the results of the query.
         */
        private function fetchData($query, $args = array()){
            $stmt = $this->prepAndExecute($query, $args);
            if(isset($stmt))
                return $stmt->fetchAll(PDO::FETCH_ASSOC);
            else
                return array();
        }

        /**
         *  A method responsible for INSERT/UPDATE/DELETE queries.
         *  @param string $query SELECT sql query to be executed.
         *  @param array $args Associative array that contains the key/value mappings of named placeholders.
         *  @return int Returns the number of affected rows.
         */
        private function modifyData($query, $args = array()){
            $stmt = $this->prepAndExecute($query, $args);
            if(isset($stmt))
                return $stmt->rowCount();
            else
                return 0;
        }

        /**
         *  @return associative array Returns information about companies that are in the database.
         */
        function findAll(){
            $getComps = "SELECT company_id, company_name, type, join_date FROM company ORDER BY company_name ASC";
            return $this->fetchData($getComps);
        }

        /**
         *  @param int $id
         *  @return associative array Returns information about company with id $id.
         */
        function findById($id){
            $getComps = "SELECT company_id, company_name, type, join_date FROM company WHERE company_id = :id";
            return $this->fetchData($getComps, array(':id' => $id));
        }

        /**
         *  @param string $type Type of companies to look for e.g. school, restaurant, etc.
         *  @return associative array Returns a list of companies of a certain type.
         */
        function findByType($type){
            $getComps = "SELECT company_id, company_name, type, join_date FROM company WHERE type = :type ORDER BY company_name ASC";
            return $this->fetchData($getComps, array(':type' => $type));
        }


        /**
         *  @return associative array Returns the id of the added company.
         */
        function save(Company $company){
            $insert_query = "INSERT INTO company (`company_id`, `company_name`, `join_date`, `type`) VALUES (NULL, :company, :join_date, :type)";
            $result = $this->modifyData($insert_query, array(':company' => $company->company_name, ':join_date' => Date('Y-m-d', strtotime($company->join_date)),
                                                            ':type' => $company->type));

            if($result > 0){
                return array('company_id' => $this->pdo->lastInsertId());
            }else{
                return array();
            }
        }

        /**
         *  @return associative array Returns the name of the company if it was deleted, empty array otherwise.
         */
        function remove(Company $company){
            $query = "DELETE FROM company WHERE `company_name` = :company";
            $result = $this->modifyData($query, array(':company' => $company->company_name));

            if($result > 0){
                return array('company_name' => $company->company_name);
            }else{
                return array();
            }
        }
    }
?>
