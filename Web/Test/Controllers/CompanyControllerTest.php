<?php
    require_once(dirname(__FILE__) . '/../../Controllers/CompanyController.php');
    require_once(dirname(__FILE__) . '/../../Data/CompanyRepository.php');
    require_once(dirname(__FILE__) . '/../../Data/CompanyPdoRepository.php');
    require_once(dirname(__FILE__) . '/../../Model/Company.php');

    class CompanyControllerTest extends PHPUnit_Framework_TestCase{
        public $comp_repo_mock;
        public $comp_controller;
        public $fixture;
        public function setUp(){
            $company1 = array('company_id' => 3, 'company_name' => 'Hunter College', 'type' => 'School', 'join_date' =>
                            '2015-12-01');
            $company2 = array('company_id' => 20, 'company_name' => 'Queens College', 'type' => 'School', 'join_date' =>
                            '2016-08-24');
            $company3 = array('company_id' => 2, 'company_name' => 'Starbucks Coffee', 'type' => 'Restaurant', 'join_date' =>
                            '2016-08-24');
            $this->fixture = array($company1, $company2, $company3);

            $this->comp_repo_mock = $this->getMockBuilder(CompanyPdoRepository::class)
                                   ->disableOriginalConstructor()
                                   ->setMethods(['findById', 'findByType', 'findAll', 'save'])
                                   ->getMock();

            $this->comp_repo_mock->method('findById')
                           ->will($this->returnValue(array($this->fixture[0])));
            $this->comp_repo_mock->method('findByType')
                           ->will($this->returnValue(array($this->fixture[0], $this->fixture[1])));
            $this->comp_repo_mock->method('findAll')
                           ->will($this->returnValue($this->fixture));
            $this->comp_repo_mock->method('save')
                           ->will($this->returnValue(array('company_id' => 10)));

            $this->comp_controller = new CompanyController($this->comp_repo_mock);
        }

        public function testGetById(){
            $expected = array('data' => array($this->fixture[0]));
            $params = array('id' => 3);
            $this->assertEquals($expected, $this->comp_controller->getData($params));
        }

        public function testGetByType(){
            $expected = array('data' => array($this->fixture[0], $this->fixture[1]));
            $params = array('type' => 'School');
            $this->assertEquals($expected, $this->comp_controller->getData($params));
        }

        public function testGetAll(){
            $expected = array('data' => $this->fixture);
            $params = array();
            $this->assertEquals($expected, $this->comp_controller->getData($params));
        }

        public function testGetFail(){
            $params = array('id' => 1, 'type' => 'School');
            $this->assertEmpty($this->comp_controller->getData($params));
        }

        public function testPostSuccess(){
            $comp_mock = $this->getMockBuilder(Company::class)
                              ->disableOriginalConstructor()
                              ->setMethods(['canBeSaved'])
                              ->getMock();
            $comp_mock->method('canBeSaved')
                      ->will($this->returnValue(true));

            $expected = array('data' => array('company_id' => 10));
            $this->assertEquals($expected, $this->comp_controller->postData($comp_mock));
        }

        public function testPostFailCompCannotBeSaved(){
            $comp_mock = $this->getMockBuilder(Company::class)
                              ->disableOriginalConstructor()
                              ->setMethods(['canBeSaved'])
                              ->getMock();
            $comp_mock->method('canBeSaved')
                      ->will($this->returnValue(false));

            $this->assertEmpty($this->comp_controller->postData($comp_mock));
        }

        public function testPostFailInvalidFormat(){
            $comp_mock = $this->getMockBuilder(Company::class)
                              ->setConstructorArgs(array(array('id' => 3)))
                              ->getMock();

            $this->assertEmpty($this->comp_controller->postData($comp_mock));
        }
    }
?>
