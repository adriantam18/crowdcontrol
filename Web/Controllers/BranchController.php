<?php
    class BranchController{
        private $branch_repo;

        function __construct(BranchRepository $branch_repo){
            $this->branch_repo = $branch_repo;
        }

        function getData($params){
            $data = array();
            $id = isset($params['id']) ? $params['id'] : "";
            $closeto = isset($params['closeto']) ? $params['closeto'] : "";
            $dist = isset($params['dist']) ? $params['dist'] : "";
            $comp = isset($params['company']) ? $params['company'] : "";
            $type = isset($params['type']) ? $params['type'] : "";

            if(!empty($id)){
                $data['data'] = $this->branch_repo->findById($id);
            }else if(!empty($comp) && empty($closeto)){
                $data['data'] = $this->branch_repo->findByCompany($comp);
            }else if(!empty($closeto)){
                $latlng = explode(',', $closeto);
                if($dist)
                    $data['data'] = $this->branch_repo->findBranchesCloseTo($latlng[0], $latlng[1], $comp, $type, $dist);
                else
                    $data['data']  = $this->branch_repo->findBranchesCloseTo($latlng[0], $latlng[1], $comp, $type);
            }else if(empty($params)){
                $data['data'] = $this->branch_repo->findAll();
            }else{
                http_response_code(400);
            }

            return $data;
        }

        function postData(Branch $branch){
            $data = array();
            if(empty($branch->id)){
                if($branch->canBeSaved())
                    $data['data'] = $this->branch_repo->save($branch);
            }else{
                http_response_code(405);;
            }

            return $data;
        }

        function putData(Branch $branch){
            $data = array();
            if(!empty($branch->id)){
                if($branch->canBeUpdated())
                    $data['data'] = $this->branch_repo->update($branch);
            }else{
                http_response_code(405);
            }

            return $data;
        }
    }
?>
