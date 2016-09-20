<?php
    interface CompanyRepository{
        public function findAll();
        public function findById($id);
        public function findByType($type);

        public function save(Company $company);
        public function remove(Company $company);
    }
?>
