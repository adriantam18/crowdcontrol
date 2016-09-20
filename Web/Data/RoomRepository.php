<?php
    interface RoomRepository{
        public function findAll();
        public function findById($id);
        public function findByBranch($branch_id, $crowd);
        public function findByCompanyAndAddress($company, $branch_address);

        public function save(Room $room);
        public function update(Room $room);
        public function remove(Room $room);
    }
?>
