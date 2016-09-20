<?php
    class BranchPdoRepository implements BranchRepository{
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
         *  @return associative array Returns information about the branches that are in the database.
         */
        function findAll(){
            $getBranches = "SELECT branch_id, address, lat, lng, zipcode,
                            DATE_FORMAT(open_hours, '%l:%i %p') AS open_hours, DATE_FORMAT(close_hours, '%l:%i %p') AS close_hours
                            FROM branch
                            ORDER BY address ASC";
            return $this->fetchData($getBranches);
        }

        /**
         *  @param int $id
         *  @return associative array Returns information about branch with id $id.
         */
        function findById($id){
            $getBranches = "SELECT branch_id, address, lat, lng, zipcode,
                            DATE_FORMAT(open_hours, '%l:%i %p') AS open_hours, DATE_FORMAT(close_hours, '%l:%i %p') AS close_hours
                            FROM branch
                            WHERE branch_id = :branch_id";
            return $this->fetchData($getBranches, array(':branch_id' => $id));
        }

        /**
         *  @param string $company_name The company to get branches of.
         *  @return associative array Returns information about the branches of a company.
         */
        function findByCompany($company_name){
            $getBranches = "SELECT c.company_name, b.branch_id, b.address, b.lat, b.lng, b.zipcode,
                            DATE_FORMAT(b.open_hours, '%l:%i %p') AS open_hours, DATE_FORMAT(b.close_hours, '%l:%i %p') AS close_hours
                            FROM company AS c
                            INNER JOIN branch AS b on b.company_id = c.company_id
                            WHERE c.company_name = :comp
                            ORDER BY b.address ASC";
            return $this->fetchData($getBranches, array(':comp' => $company_name));
        }

        /**
         *  @param float $lat Latitude of a branch.
         *  @param float $lng Longitude of a branch.
         *  @param string $company_name Company to get branches of.
         *  @param string $type Type of company to search for e.g. school, restaurant, etc..
         *  @param int $dist Maximum distance of branches from the given latitude and longitude.
         *  @return associative array Returns information about branches that are closer than the given distance.
         */
        function findBranchesCloseTo($lat, $lng, $company_name, $type, $dist = 5){
            $getBranches = "SELECT c.company_name, b.branch_id, b.address, b.zipcode, b.lat, b.lng,
                            TRUNCATE((3959 * acos(cos(radians(:lat)) * cos(radians(b.lat)) * cos(radians(b.lng) - radians(:lng)) +
                            sin(radians(:lat)) * sin(radians(b.lat)))), 2) AS distance,
                            DATE_FORMAT(b.open_hours, '%l:%i %p') AS open_hours, DATE_FORMAT(b.close_hours, '%l:%i %p') AS close_hours
                            FROM company AS c
                            INNER JOIN branch AS b on c.company_id = b.company_id
                            HAVING distance <= :dist";

            if(!empty($company_name)){
                $getBranches .= " AND c.company_name = :comp ORDER BY distance";
                return $this->fetchData($getBranches, array(':lat' => $lat, ':lng' => $lng, ':comp' => $company_name, ':dist' => $dist));
            }else if(!empty($type) && empty($company_name)){
                $getBranches .= " AND c.type = :type ORDER BY distance";
                return $this->fetchData($getBranches, array(':lat' => $lat, ':lng' => $lng, ':type' => $type, ':dist' => $dist));
            }else{
                $getBranches .= " ORDER BY distance";
                return $this->fetchData($getBranches, array(':lat' => $lat, ':lng' => $lng, ':dist' => $dist));
            }
        }

        /**
         *  @return associative array Returns the id of the added branch.
         */
        function save(Branch $branch){
            $sub_query = "SELECT `company_id` FROM company
                          WHERE `company_name` = :company";

            $row = $this->fetchData($sub_query, array(':company' => $branch->company));
            if($row){
                $company_id = $row[0]['company_id'];

                $insert_branch = "INSERT INTO branch (`branch_id`, `company_id`, `address`, `zipcode`, `lat`, `lng`,
                                                    `open_hours`, `close_hours`)
                                VALUES (NULL, :company_id, :address, :zipcode, :lat, :lng, :open_hrs, :close_hrs)";
                $args = array(':company_id' => $company_id, ':address' => $branch->address, ':zipcode' => $branch->zipcode,
                            ':lat' => $branch->lat, ':lng' => $branch->lng, ':open_hrs' => $branch->open_hours,
                            ':close_hrs' => $branch->close_hours);
                $result = $this->modifyData($insert_branch, $args);

                if($result > 0){
                    return array('branch_id' => $this->pdo->lastInsertId());
                }else{
                    return array();
                }
            }else{
                return array();
            }
        }

        /**
         *  @return associative array Returns the id of the updated branch.
         */
        function update(Branch $branch){
            $query = "UPDATE branch SET
                    `address` = COALESCE(NULLIF(:address, ''), address),
                    `zipcode` = COALESCE(NULLIF(:zipcode, ''), zipcode),
                    `lat` = COALESCE(NULLIF(:lat, ''), lat),
                    `lng` = COALESCE(NULLIF(:lng, ''), lng),
                    `open_hours` = COALESCE(NULLIF(:open_hours, ''), open_hours),
                    `close_hours` = COALESCE(NULLIF(:close_hours, ''), close_hours)
                    WHERE `branch_id` = :branch_id";
            $args = array(':branch_id' => $branch->id, ':address' => $branch->address, ':zipcode' => $branch->zipcode,
                        ':lat' => $branch->lat, ':lng' => $branch->lng, ':open_hours' => $branch->open_hours,
                        ':close_hours' => $branch->close_hours);
            $result = $this->modifyData($query, $args);

            if($result > 0){
                return array('branch_id' => $branch->id);
            }else{
                return array();
            }
        }

        /**
         *  @return associative array Returns the id of the deleted branch.
         */
        function remove(Branch $branch){
            $query = "DELETE FROM branch WHERE `branch_id` = :branch_id";
            $result = $this->modifyData($query, array(':branch_id' => $branch->id));

            if($result > 0){
                return array('branch_id' => $branch->id);
            }else{
                return array();
            }
        }
    }
?>
