<?php
    class RoomPdoRepository implements RoomRepository{
        private $pdo;

        public function __construct($pdo){
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
         *  Checks if the password matches with the hashed password stored in the database.
         *  @param string $pass The password that was sent by the class_implements.
         *  @param string $auth The hashed password stored in the database.
         *  @return boolean Returns true if the password and auth matches, false otherwise.
         */
        private function checkPass($pass, $auth){
            if(!password_verify($pass, $auth)){
                return false;
            }else{
                return true;
            }
        }

        /**
         *  @return associative array Returns information about rooms including crowd percentage
         *          and last date and time each room's data was updated.
         */
        public function findAll(){
            $get_rooms = "SELECT room_id, branch_id, room_number,
                        (CASE
                            WHEN (crowd / max_capacity) * 100 > 100 THEN 100
                            WHEN (crowd / max_capacity) * 100 < 0 THEN 0
                            ELSE TRUNCATE((crowd / max_capacity) * 100, 0)
                        END) AS crowd, DATE_FORMAT(time, '%h:%i %p') AS time, date
                        FROM room";
            return $this->fetchData($get_rooms);
        }

        /**
         *  @param int $id
         *  @return associative array Returns information about the room including crowd percentage
         *          and last date and time data was updated.
         */
        public function findById($id){
            $get_rooms = "SELECT room_id, branch_id, room_number,
                        (CASE
                            WHEN (crowd / max_capacity) * 100 > 100 THEN 100
                            WHEN (crowd / max_capacity) * 100 < 0 THEN 0
                            ELSE TRUNCATE((crowd / max_capacity) * 100, 0)
                        END) AS crowd, DATE_FORMAT(time, '%h:%i %p') AS time, date
                        FROM room WHERE room_id = :id";

            return $this->fetchData($get_rooms, array(':id' => $id));
        }

        /**
         *  @param string $branch Street address of the branch to get rooms from.
         *  @param int $crowd Maximum percentage of crowd to filter the rooms with. Ignored if crowd is empty.
         *  @return associative array Returns information about rooms from the given branch address. Disregards
         *          rooms with crowd greater than the given crowd percentage.
         */
        public function findByBranch($branch_id, $crowd = ""){
            $get_rooms = "SELECT room_id, branch_id, room_number,
                        (CASE
                            WHEN (crowd / max_capacity) * 100 > 100 THEN 100
                            WHEN (crowd / max_capacity) * 100 < 0 THEN 0
                            ELSE TRUNCATE((crowd / max_capacity) * 100, 0)
                        END) AS crowd, DATE_FORMAT(time, '%h:%i %p') AS time, date
                        FROM room
                        WHERE branch_id = :branch_id";

            if(!empty($crowd)){
                $get_rooms .= " HAVING crowd <= :crowd ORDER BY crowd, room_number";
                return $this->fetchData($get_rooms, array(':branch_id' => $branch_id, ':crowd' => $crowd));
            }else{
                $get_rooms .= " ORDER BY crowd, room_number";
                return $this->fetchData($get_rooms, array(':branch_id' => $branch_id));
            }
        }

        /**
         *  @param string $company Name of the company to get rooms from.
         *  @return associative array Returns information about rooms that are from the company including
         *          crowd percentage and last date and time each room's data was updated.
         */
        public function findByCompanyAndAddress($company, $branch_address){
            $get_rooms = "SELECT r.room_id, r.branch_id, r.room_number,
                        (CASE
                            WHEN (crowd / max_capacity) * 100 > 100 THEN 100
                            WHEN (crowd / max_capacity) * 100 < 0 THEN 0
                            ELSE TRUNCATE((crowd / max_capacity) * 100, 0)
                        END) AS crowd, DATE_FORMAT(r.time, '%h:%i %p') AS time, r.date
                        FROM room r
                        INNER JOIN branch b on b.branch_id = r.branch_id
                        INNER JOIN company c on c.company_id = b.company_id
                        WHERE c.company_name = :company AND b.address = :address
                        ORDER BY crowd, room_number";

            return $this->fetchData($get_rooms, array(':company' => $company, ':address' => $branch_address));
        }

        public function save(Room $room){
            $sub_query = "SELECT c.company_id, b.branch_id FROM company AS c
                          INNER JOIN branch b on b.company_id = c.company_id
                          WHERE b.address = :address AND c.company_name = :company";

            $row = $this->fetchData($sub_query, array(':address' => $room->address, ':company' => $room->company));

            if($row){
                $branch_id = $row[0]['branch_id'];1
                $hash = password_hash($room->password, PASSWORD_DEFAULT);

                $insert_room = "INSERT INTO room (`room_id`, `branch_id`, `room_number`, `max_capacity`, `password`)
                                VALUES (NULL, :branch_id, :room, :max_cap, :hash)";
                $args = array(':branch_id' => $branch_id, ':room' => $room->room_number, ':max_cap' => $room->max_cap,
                            ':hash' => $hash);
                $result = $this->modifyData($insert_room, $args);

                if($result > 0){
                    return array('room_id' => $this->pdo->lastInsertId(), 'password' => $room->password);
                }else{
                    return array();
                }
            }else{
                return array();
            }
        }

        public function update(Room $room){
            $query = "SELECT `room_id`, `password`, `crowd` FROM room WHERE `room_id` = :room_id";
            $row = $this->fetchData($query, array(':room_id' => $room->id));

            if($row){
                if($this->checkPass($room->password, $row[0]['password'])){
                    $crowd = isset($room->crowd) && !empty($room->crowd) ?  $row[0]['crowd'] + $room->crowd : 0;
                    $update_time = date('H:i:s', strtotime($room->time));

                    $update = "UPDATE room SET `crowd` = :crowd, `date` = :date, `time` = :time
                               WHERE `room_id` = :id";
                    $args = array(':crowd' => $crowd, ':date' => $room->date, ':time' => $update_time,
                                ':id' => $room->id);
                    $result = $this->modifyData($update, $args);

                    if($result > 0){
                        return array('room_id' => $room->id);
                    }else{
                        return array();
                    }
                }else{
                    return null;
                }
            }else{
                return array();
            }
        }

        function remove(Room $room){
            $query = "SELECT `room_id`, `password` FROM room WHERE `room_id` = :room_id";
            $row = $this->fetchData($query, array(':room_id' => $room->id));

            if($row){
                if($this->checkPass($room->password, $row[0]['password'])){
                    $query = "DELETE FROM room WHERE `room_id` = :id";
                    $result = $this->modifyData($query, array(':id' => $room->id));

                    if($result > 0){
                        return array('room_id' => $room->id);
                    }else{
                        return array();
                    }
                }else{
                    return array();
                }
            }else{
                return array();
            }
        }
    }
?>
