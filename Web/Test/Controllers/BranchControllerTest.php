<?php
    require_once(dirname(__FILE__) . '/../../Controllers/BranchController.php');
    require_once(dirname(__FILE__) . '/../../Data/BranchRepository.php');
    require_once(dirname(__FILE__) . '/../../Data/BranchPdoRepository.php');
    require_once(dirname(__FILE__) . '/../../Model/Branch.php');

    class BranchControllerTest extends PHPUnit_Framework_TestCase{
        public $branch_repo_mock;
        public $branch_controller;
        public $fixture;

        public function setUp(){
            $branch1 = array('branch_id' => 1, 'company_id' => 3, 'address' => '695 Park Ave New York NY', 'zipcode' => '10065',
                            'lat' => '40.768608', 'lng' => '-73.965050', 'open_hours' => '08:00 AM', 'close_hours' => '06:00 PM');
            $branch2 = array('branch_id' => 2, 'company_id' => 4, 'address' => '695 Park Ave New York NY', 'zipcode' => '10065',
                            'lat' => '40.768608', 'lng' => '-73.965050', 'open_hours' => '08:00 AM', 'close_hours' => '06:00 PM');
            $branch3 = array('branch_id' => 3, 'company_id' => 4, 'address' => '2034 Sunrise Highway Valley Stream NY', 'zipcode' =>
                            '11581', 'lat' => '40.662891', 'lng' => '-73.712280', 'open_hours' => '06:00 AM', 'close_hours' =>
                            '10:00 PM');
            $this->fixture = array($branch1, $branch2, $branch3);

            $this->branch_repo_mock = $this->getMockBuilder(BranchPdoRepository::class)
                                   ->disableOriginalConstructor()
                                   ->setMethods(['findById', 'findByCompany', 'findBranchesCloseTo', 'findAll', 'save', 'update'])
                                   ->getMock();

            $this->branch_repo_mock->method('findById')
                           ->will($this->returnValue(array($this->fixture[0])));

            $this->branch_repo_mock->method('findByCompany')
                           ->will($this->returnValue(array($this->fixture[1], $this->fixture[2])));

            $this->branch_repo_mock->method('findBranchesCloseTo')
                           ->will($this->returnValue(array($this->fixture[1])));

            $this->branch_repo_mock->method('findAll')
                           ->will($this->returnValue($this->fixture));

            $this->branch_repo_mock->method('save')
                           ->will($this->returnValue(array('branch_id' => 10)));

            $this->branch_repo_mock->method('update')
                           ->will($this->returnValue(array('branch_id' => 20)));

            $this->branch_controller = new BranchController($this->branch_repo_mock);
        }

        public function testGetById(){
            $expected = array('data' => array($this->fixture[0]));
            $params = array('id' => 1);
            $this->assertEquals($expected, $this->branch_controller->getData($params));
        }

        public function testGetByCompany(){
            $expected = array('data' => array($this->fixture[1], $this->fixture[2]));
            $params = array('company' => 'Starbucks Coffee');
            $this->assertEquals($expected, $this->branch_controller->getData($params));
        }

        public function testGetByBranchesCloseTo(){
            $expected = array('data' => array($this->fixture[1]));
            $params = array('closeto' => 'Random,coordinate', 'company' => 'Hunter College', 'dist' => 10);
            $this->assertEquals($expected, $this->branch_controller->getData($params));

            $params = array('closeto' => 'Random,coordinate', 'company' => 'Hunter College');
            $this->assertEquals($expected, $this->branch_controller->getData($params));

            $params = array('closeto' => 'Random,coordinate', 'dist' => 10);
            $this->assertEquals($expected, $this->branch_controller->getData($params));

            $params = array('closeto' => 'Random,coordinate', 'company' => 'Hunter College', 'dist' => 10, 'type' => 'test');
            $this->assertEquals($expected, $this->branch_controller->getData($params));
        }

        public function testGetAll(){
            $expected = array('data' => $this->fixture);
            $params = array();
            $this->assertEquals($expected, $this->branch_controller->getData($params));
        }

        public function testGetFail(){
            $params = array('dist' => 10);
            $this->assertEmpty($this->branch_controller->getData($params));
        }

        public function testPostSuccess(){
            $branch_mock = $this->getMockBuilder(Branch::class)
                              ->disableOriginalConstructor()
                              ->setMethods(['canBeSaved'])
                              ->getMock();
            $branch_mock->method('canBeSaved')
                      ->will($this->returnValue(true));

            $expected = array('data' => array('branch_id' => 10));
            $this->assertEquals($expected, $this->branch_controller->postData($branch_mock));
        }

        public function testPostFailBranchCannotBeSaved(){
            $branch_mock = $this->getMockBuilder(Branch::class)
                              ->disableOriginalConstructor()
                              ->setMethods(['canBeSaved'])
                              ->getMock();
            $branch_mock->method('canBeSaved')
                      ->will($this->returnValue(false));

            $this->assertEmpty($this->branch_controller->postData($branch_mock));
        }

        public function testPostFailInvalidFormat(){
            $branch_mock = $this->getMockBuilder(Branch::class)
                              ->setConstructorArgs(array(array('id' => 3)))
                              ->getMock();

            $this->assertEmpty($this->branch_controller->postData($branch_mock));
        }

        public function testPutSuccess(){
            $branch_mock = $this->getMockBuilder(Branch::class)
                              ->setConstructorArgs(array(array('id' => 20)))
                              ->getMock();
            $branch_mock->method('canBeUpdated')
                      ->will($this->returnValue(true));

            $expected = array('data' => array('branch_id' => 20));
            $this->assertEquals($expected, $this->branch_controller->putData($branch_mock));
        }

        public function testPutFailBranchCannotBeUpdated(){
            $branch_mock = $this->getMockBuilder(Branch::class)
                              ->setConstructorArgs(array(array('id' => 20)))
                              ->getMock();
            $branch_mock->method('canBeUpdated')
                      ->will($this->returnValue(false));

            $this->assertEmpty($this->branch_controller->putData($branch_mock));
        }

        public function testPutFailInvalidFormat(){
            $branch_mock = $this->getMockBuilder(Branch::class)
                              ->disableOriginalConstructor()
                              ->getMock();
            $branch_mock->method('canBeUpdated')
                      ->will($this->returnValue(true));

            $this->assertEmpty($this->branch_controller->putData($branch_mock));
        }
    }
?>
