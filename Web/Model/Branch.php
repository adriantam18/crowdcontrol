<?php
    class Branch{
        public $id;
        public $company;
        public $address;
        public $zipcode;
        public $lat;
        public $lng;
        public $open_hours;
        public $close_hours;

        public function __construct($data = array()){
            $this->id = isset($data['id']) ? $data['id'] : "";
            $this->company = isset($data['company_name']) ? $data['company_name'] : "";
            $this->address = isset($data['address']) ? $data['address'] : "";
            $this->zipcode = isset($data['zipcode']) ? $data['zipcode'] : "";
            $this->lat = isset($data['lat']) ? $data['lat'] : "";
            $this->lng = isset($data['lng']) ? $data['lng'] : "";
            $this->open_hours = isset($data['open_hours']) ? $data['open_hours'] : "";
            $this->close_hours = isset($data['close_hours']) ? $data['close_hours'] : "";
        }

        public function canBeSaved(){
            return (!empty($this->address) && !empty($this->company) && !empty($this->zipcode) && empty($this->id));
        }

        public function canBeUpdated(){
            return (!empty($this->id));
        }
    }
?>
zipcodecode
