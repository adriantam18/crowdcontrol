<?php
    require_once(dirname(__FILE__) . '/../../Controllers/RoomController.php');
    require_once(dirname(__FILE__) . '/../../Data/RoomRepository.php');
    require_once(dirname(__FILE__) . '/../../Data/RoomPdoRepository.php');
    require_once(dirname(__FILE__) . '/../../Model/Room.php');

    class RoomControllerTest extends PHPUnit_Framework_TestCase{
        public $room_repo_mock;
        public $room_controller;
        public $fixture;

        public function setUp(){
            $room1 = array('room_id' => 1, 'branch_id' => 1, 'room_number' => '1000J', 'crowd' => 100, 'time' => '00:00 AM',
                        'date' => '0000-00-00');
            $room2 = array('room_id' => 2, 'branch_id' => 1, 'room_number' => '1000A', 'crowd' => 50, 'time' => '11:11 AM',
                        'date' => '1111-11-11');
            $room3 = array('room_id' => 3, 'branch_id' => 2, 'room_number' => '505', 'crowd' => 25, 'time' => '22:22 AM',
                        'date' => '2222-22-22');
            $this->fixture = array($room1, $room2, $room3);

            $this->room_repo_mock = $this->getMockBuilder(RoomPdoRepository::class)
                                   ->disableOriginalConstructor()
                                   ->setMethods(['findById', 'findByBranch', 'findAll', 'save', 'update', 'remove'])
                                   ->getMock();

            $this->room_repo_mock->method('findById')
                           ->will($this->returnValue(array($this->fixture[0])));

            $this->room_repo_mock->method('findByBranch')
                           ->will($this->returnValue(array($this->fixture[0], $this->fixture[1])));

            $this->room_repo_mock->method('findAll')
                           ->will($this->returnValue($this->fixture));

            $this->room_repo_mock->method('save')
                           ->will($this->returnValue(array('room_id' => 1)));

            $this->room_repo_mock->method('update')
                           ->will($this->returnValue(array('room_id' => 2)));

            $this->room_repo_mock->method('remove')
                           ->will($this->returnValue(array('room_id' => 3)));

            $this->room_controller = new RoomController($this->room_repo_mock);
        }

        public function testGetById(){
            $expected = array('data' => array($this->fixture[0]));
            $params = array('id' => 1);
            $this->assertEquals($expected, $this->room_controller->getData($params));
        }

        public function testGetByBranch(){
            $expected = array('data' => array($this->fixture[0], $this->fixture[1]));
            $params = array('branch_id' => 1);
            $this->assertEquals($expected, $this->room_controller->getData($params));
        }

        public function testGetByBranchesWithCrowd(){
            $expected = array('data' => array($this->fixture[0], $this->fixture[1]));
            $params = array('branch_id' => 1, 'crowd' => 10);
            $this->assertEquals($expected, $this->room_controller->getData($params));
        }

        public function testGetAll(){
            $expected = array('data' => $this->fixture);
            $params = array();
            $this->assertEquals($expected, $this->room_controller->getData($params));
        }

        public function testGetFail(){
            $params = array('id' => 1, 'branch_id' => 2);
            $this->assertEmpty($this->room_controller->getData($params));
        }

        public function testPostSuccess(){
            $room_mock = $this->getMockBuilder(Room::class)
                              ->disableOriginalConstructor()
                              ->setMethods(['canBeSaved'])
                              ->getMock();
            $room_mock->method('canBeSaved')
                      ->will($this->returnValue(true));

            $expected = array('data' => array('room_id' => 1));
            $this->assertEquals($expected, $this->room_controller->postData($room_mock));
        }

        public function testPostFailBranchCannotBeSaved(){
            $room_mock = $this->getMockBuilder(Room::class)
                              ->disableOriginalConstructor()
                              ->setMethods(['canBeSaved'])
                              ->getMock();
            $room_mock->method('canBeSaved')
                      ->will($this->returnValue(false));

            $this->assertEmpty($this->room_controller->postData($room_mock));
        }

        public function testPostFailInvalidFormat(){
            $room_mock = $this->getMockBuilder(Room::class)
                              ->setConstructorArgs(array(array('id' => 1)))
                              ->getMock();

            $this->assertEmpty($this->room_controller->postData($room_mock));
        }

        public function testpatchSuccess(){
            $room_mock = $this->getMockBuilder(Room::class)
                              ->setConstructorArgs(array(array('id' => 2)))
                              ->getMock();
            $room_mock->method('canBeUpdated')
                      ->will($this->returnValue(true));

            $expected = array('data' => array('room_id' => 2));
            $this->assertEquals($expected, $this->room_controller->patchData($room_mock));
        }

        public function testpatchFailBranchCannotBeUpdated(){
            $room_mock = $this->getMockBuilder(Room::class)
                              ->setConstructorArgs(array(array('id' => 2)))
                              ->getMock();
            $room_mock->method('canBeUpdated')
                      ->will($this->returnValue(false));

            $this->assertEmpty($this->room_controller->patchData($room_mock));
        }

        public function testpatchFailInvalidFormat(){
            $room_mock = $this->getMockBuilder(Room::class)
                              ->disableOriginalConstructor()
                              ->getMock();
            $room_mock->method('canBeUpdated')
                      ->will($this->returnValue(true));

            $this->assertEmpty($this->room_controller->patchData($room_mock));
        }

        public function testRemoveSuccess(){
            $room_mock = $this->getMockBuilder(Room::class)
                              ->setConstructorArgs(array(array('id' => 3)))
                              ->getMock();

            $expected = array('data' => array('room_id' => 3));
            $this->assertEquals($expected, $this->room_controller->deleteData($room_mock));
        }

        public function testRemoveFail(){
            $room_mock = $this->getMockBuilder(Room::class)
                              ->disableOriginalConstructor()
                              ->getMock();
            $this->assertEmpty($this->room_controller->deleteData($room_mock));
        }
    }
?>
