<?php
    class Room{
        public $id;
        public $company;
        public $address;
        public $room_number;
        public $crowd;
        public $max_cap;
        public $date;
        public $time;
        public $password;

        public function __construct($data = array()){
            $this->id = isset($data['id']) ? $data['id'] : "";
            $this->company = isset($data['company_name']) ? $data['company_name'] : "";
            $this->address = isset($data['address']) ? $data['address'] : "";
            $this->room_number = isset($data['room_number']) ? $data['room_number'] : "";
            $this->crowd = isset($data['crowd']) ? $data['crowd'] : "";
            $this->max_cap = isset($data['max_cap']) ? $data['max_cap'] : "";
            $this->date = isset($data['date']) ? $data['date'] : "";
            $this->time = isset($data['time']) ? $data['time'] : "";
            $this->password = isset($data['password']) ? $data['password'] : "";
        }

        public function canBeSaved(){
            return (!empty($this->company) && !empty($this->address) &&
                    !empty($this->max_cap) && !empty($this->room_number) &&
                    !empty($this->password));
        }

        public function canBeUpdated(){
            return (!empty($this->id) && !empty($this->password) && !empty($this->time) && !empty($this->date));
        }
    }
?>
