<?php
    interface BranchRepository{
        public function findAll();
        public function findById($id);
        public function findByCompany($company);

        public function save(Branch $branch);
        public function update(Branch $branch);
        public function remove(Branch $branch);
    }
?>
