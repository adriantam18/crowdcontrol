<?php
    class Company {
        public $id;
        public $company_name;
        public $type;
        public $join_date;

        public function __construct($data = array()){
            $this->id = isset($data['id']) ? $data['id'] : "";
            $this->company_name = isset($data['company_name']) ? $data['company_name'] : "";
            $this->type = isset($data['type']) ? $data['type'] : "";
            $this->join_date = isset($data['join_date']) ? $data['join_date'] : "";
        }

        public function canBeSaved(){
            return (!empty($this->company_name) && !empty($this->type) && !empty($this->join_date));
        }
    }
?>
