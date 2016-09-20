<?php
    class RoomController{
        private $room_repo;

        function __construct(RoomRepository $room_repo){
            $this->room_repo = $room_repo;
        }

        function getData($params){
            $data = array();
            $id = isset($params['id']) ? $params['id'] : "";
            $branch_id = isset($params['branch_id']) ? $params['branch_id'] : "";
            $crowd = isset($params['crowd']) ? $params['crowd'] : "";

            if(!empty($id) && empty($branch_id) && empty($crowd)){
                $data['data'] = $this->room_repo->findById($id);
            }else if(!empty($branch_id) && empty($id)){
                $data['data'] = $this->room_repo->findByBranch($branch_id, $crowd);
            }else if(empty($branch_id) && empty($id)){
                $data['data'] = $this->room_repo->findAll();
            }else{
                http_response_code(400);
            }

            return $data;
        }

        function postData(Room $room){
            $data = array();
            if(empty($room->id)){
                $room->password = $this->genRandom();
                if($room->canBeSaved())
                    $data['data'] = $this->room_repo->save($room);
            }else{
                http_response_code(405);
            }

            return $data;
        }

        function patchData(Room $room){
            $data = array();
            if(!empty($room->id)){
                if($room->canBeUpdated())
                    $data['data'] = $this->room_repo->update($room);
            }else{
                http_response_code(405);
            }

            return $data;
        }

        function deleteData(Room $room){
            $data = array();
            if($room->id){
                $data['data'] = $this->room_repo->remove($room);
            }else{
                http_response_code(405);
            }

            return $data;
        }



        /**
         *  Generates a random alphanumeric string.
         *  @return string Random string.
         */
        private function genRandom(){
            $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
            $chars_length = strlen($characters);
            $random_string = '';
            $length = rand(5, 10);
            for ($i = 0; $i < $length; $i++) {
                $random_string .= $characters[rand(0, $chars_length - 1)];
            }
            return $random_string;
        }
    }

?>
