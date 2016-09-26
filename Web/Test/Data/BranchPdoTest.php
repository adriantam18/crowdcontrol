<?php
    require_once(dirname(__FILE__) . '/../../Data/BranchRepository.php');
    require_once(dirname(__FILE__) . '/../../Data/BranchPdoRepository.php');
    require_once(dirname(__FILE__) . '/../../Model/Branch.php');

    class BranchPdoTest extends PHPUnit_Extensions_Database_TestCase{
        static private $pdo = null;
        private $conn = null;
        static private $branch_repo = null;

        public static function setUpBeforeClass(){
            self::$pdo = new PDO($GLOBALS['DB_DSN'], $GLOBALS['DB_USER'], $GLOBALS['DB_PASS']);
            self::$branch_repo = new BranchPdoRepository(self::$pdo);
        }

        public static function tearDownAfterClass(){
            self::$pdo = null;
            self::$branch_repo = null;
        }

        final public function getConnection(){
            if($this->conn == null){
                if(self::$pdo == null){
                    self::$pdo = new PDO($GLOBALS['DB_DSN'], $GLOBALS['DB_USER'], $GLOBALS['DB_PASS']);
                    self::$branch_repo = new BranchPdoRepository(self::$pdo);
                }
                $this->conn = $this->createDefaultDBConnection(self::$pdo, $GLOBALS['DB_DBNAME']);
            }

            return $this->conn;
        }

        protected function getDataSet(){
            return $this->createXMLDataSet(dirname(__FILE__) . '/BranchDataSet/Seed.xml');
        }

        public function testFindByIdSuccess(){
            $result = self::$branch_repo->findById(2);
            $expected = array(array('branch_id' => 2, 'address' => '695 Park Ave New York NY', 'lat' => '40.768608',
                            'lng' => '-73.965050', 'zipcode' => '10065', 'open_hours' => '8:00 AM', 'close_hours' =>
                            '6:00 PM'));
            $this->assertEquals($expected, $result);
        }

        public function testFindByIdWithInvalidId(){
            $result = self::$branch_repo->findById(100);
            $expected = array();
            $this->assertEquals($expected, $result);
        }

        public function testFindByCompanySuccess(){
            $result = self::$branch_repo->findByCompany('Starbucks Coffee');
            $branch1 = array('company_name' => 'Starbucks Coffee', 'branch_id' => 3, 'address' => '2034 Sunrise Highway Valley Stream NY',
                        'lat' => '40.662891', 'lng' => '-73.712280', 'zipcode' => '11581', 'open_hours' => '6:00 AM', 'close_hours' =>
                        '10:00 PM');
            $branch2 = array('company_name' => 'Starbucks Coffee', 'branch_id' => 2, 'address' => '695 Park Ave New York NY',
                        'lat' => '40.768608', 'lng' => '-73.965050', 'zipcode' => '10065', 'open_hours' => '8:00 AM', 'close_hours' =>
                        '6:00 PM');
            $expected = array($branch1, $branch2);
            $this->assertEquals($expected, $result);
        }

        public function testFindByCompanyWithInvalidCompany(){
            $result = self::$branch_repo->findByCompany('Does not exist');
            $expected = array();
            $this->assertEquals($expected, $result);
        }

        public function testSaveSuccess(){
            $result = self::$branch_repo->save(new Branch(array('company_name' => 'Starbucks Coffee', 'address' =>
                                                        '500 Sunrise Highway Valley Stream NY', 'zipcode' => '11581',
                                                        'lat' => '40.66463', 'lng' => '-73.720747', 'open_hours' => '10:00:00',
                                                        'close_hours' => '22:00:00')));
            $this->assertEquals(array('branch_id' => 4), $result);

            $expected = $this->createXmlDataSet(dirname(__FILE__) . "/BranchDataSet/BranchInsert.xml");
            $actual = new PHPUnit_Extensions_Database_DataSet_QueryDataSet($this->getConnection());
            $actual->addTable('branch');
            $this->assertDataSetsEqual($expected, $actual, "Database mismatch");
        }

        public function testSaveFail(){
            $result = self::$branch_repo->save(new Branch(array('company_name' => 'Does not exist', 'address' =>
                                                        '500 Sunrise Highway Valley Stream NY', 'zipcode' => '11581',
                                                        'lat' => '40.66463', 'lng' => '-73.720747', 'open_hours' => '10:00:00',
                                                        'close_hours' => '22:00:00')));
            $this->assertEquals(array(), $result);

            $expected = $this->createXmlDataSet(dirname(__FILE__) . "/BranchDataSet/Seed.xml");
            $actual = new PHPUnit_Extensions_Database_DataSet_QueryDataSet($this->getConnection());
            $actual->addTable('branch');
            $this->assertDataSetsEqual($expected, $actual, "Database mismatch");
        }

        public function testUpdateSuccess(){
            $result = self::$branch_repo->update(new Branch(array('id' => 2, 'close_hours' => '23:59:00')));
            $this->assertEquals(array('branch_id' => 2), $result);

            $expected = $this->createXmlDataSet(dirname(__FILE__) . "/BranchDataSet/BranchUpdate.xml");
            $actual = new PHPUnit_Extensions_Database_DataSet_QueryDataSet($this->getConnection());
            $actual->addTable('branch');
            $this->assertDataSetsEqual($expected, $actual, "Database mismatch");
        }

        public function testUpdateFail(){
            $result = self::$branch_repo->update(new Branch(array('id' => 100, 'close_hours' => '23:59:00')));
            $this->assertEquals(array(), $result);

            $expected = $this->createXmlDataSet(dirname(__FILE__) . "/BranchDataSet/Seed.xml");
            $actual = new PHPUnit_Extensions_Database_DataSet_QueryDataSet($this->getConnection());
            $actual->addTable('branch');
            $this->assertDataSetsEqual($expected, $actual, "Database mismatch");
        }

        public function testRemoveSuccess(){
            $result = self::$branch_repo->remove(new Branch(array('id' => 2)));
            $this->assertEquals(array('branch_id' => 2), $result);

            $expected = $this->createXmlDataSet(dirname(__FILE__) . "/BranchDataSet/BranchRemove.xml");
            $actual = new PHPUnit_Extensions_Database_DataSet_QueryDataSet($this->getConnection());
            $actual->addTable('branch');
            $this->assertDataSetsEqual($expected, $actual, "Database mismatch");
        }

        public function testRemoveFail(){
            $result = self::$branch_repo->remove(new Branch(array('id' => 100)));
            $this->assertEquals(array(), $result);

            $expected = $this->createXmlDataSet(dirname(__FILE__) . "/BranchDataSet/Seed.xml");
            $actual = new PHPUnit_Extensions_Database_DataSet_QueryDataSet($this->getConnection());
            $actual->addTable('branch');
            $this->assertDataSetsEqual($expected, $actual, "Database mismatch");
        }
    }
?>
