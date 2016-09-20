<?php
    class CompanyController{
        private $company_repo;

        function __construct(CompanyRepository $company_repo){
            $this->company_repo = $company_repo;
        }

        function getData($params){
            $data = array();
            $id = !empty($params['id']) ? $params['id'] : "";
            $type = !empty($params['type']) ? $params['type'] : "";

            if(!empty($id) && empty($type)){
                $data['data'] = $this->company_repo->findById($id);
            }else if(!empty($type) && empty($id)){
                $data['data'] = $this->company_repo->findByType($type);
            }else if(empty($type) && empty($id)){
                $data['data'] = $this->company_repo->findAll();
            }else{
                http_response_code(400);
            }

            return $data;
        }

        function postData(Company $company){
            $data = array();
            if(empty($company->id)){
                if($company->canBeSaved())
                    $data['data'] = $this->company_repo->save($company);
            }else{
                http_response_code(405);
            }

            return $data;
        }
    }

?>
