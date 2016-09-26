<?php
    require_once(dirname(__FILE__) . '/../../Data/CompanyRepository.php');
    require_once(dirname(__FILE__) . '/../../Data/CompanyPdoRepository.php');
    require_once(dirname(__FILE__) . '/../../Model/Company.php');

    class CompanyPdoTest extends PHPUnit_Extensions_Database_TestCase{
        static private $pdo = null;
        private $conn = null;
        static private $comp_repo = null;

        public static function setUpBeforeClass(){
            self::$pdo = new PDO($GLOBALS['DB_DSN'], $GLOBALS['DB_USER'], $GLOBALS['DB_PASS']);
            self::$comp_repo = new CompanyPdoRepository(self::$pdo);
        }

        public static function tearDownAfterClass(){
            self::$pdo = null;
            self::$comp_repo = null;
        }

        final public function getConnection(){
            if($this->conn == null){
                if(self::$pdo == null){
                    self::$pdo = new PDO($GLOBALS['DB_DSN'], $GLOBALS['DB_USER'], $GLOBALS['DB_PASS']);
                    self::$comp_repo = new CompanyPdoRepository(self::$pdo);
                }
                $this->conn = $this->createDefaultDBConnection(self::$pdo, $GLOBALS['DB_DBNAME']);
            }

            return $this->conn;
        }

        protected function getDataSet(){
            return $this->createXMLDataSet(dirname(__FILE__) . '/CompanyDataSet/Seed.xml');
        }

        public function testFindByIdSuccess(){
            $result = self::$comp_repo->findById(3);
            $expected = array(array('company_id' => 3, 'company_name' => 'Hunter College', 'type' => 'School',
                            'join_date' => "2015-12-01"));
            $this->assertEquals($expected, $result);
        }

        public function testFindByIdWithInvalidId(){
            $result = self::$comp_repo->findById(100);
            $expected = array();
            $this->assertEquals($expected, $result);
        }

        public function testFindByTypeSuccess(){
            $result = self::$comp_repo->findByType('Restaurant');
            $expected = array(array('company_id' => 9, 'company_name' => 'Lipa Grill', 'type' => 'Restaurant',
                            'join_date' => "2016-08-23"), array('company_id' => 4, 'company_name' => 'Starbucks Coffee',
                            'type' => 'Restaurant', 'join_date' => '2015-11-30'));
            $this->assertEquals($expected, $result);

            $result = self::$comp_repo->findByType('School');
            $expected = array(array('company_id' => 3, 'company_name' => 'Hunter College', 'type' => 'School',
                            'join_date' => "2015-12-01"), array('company_id' => 10, 'company_name' => 'Queens College',
                            'type' => 'School', 'join_date' => '2016-08-24'));
            $this->assertEquals($expected, $result);
        }

        public function testFindByTypeWithInvalidType(){
            $result = self::$comp_repo->findByType('Club');
            $expected = array();
            $this->assertEquals($expected, $result);
        }

        public function testSaveSuccess(){
            $result = self::$comp_repo->save(new Company(array('company_name' => 'Kuya Clyde', 'type' => 'Restaurant',
                                    'join_date' => "2016-08-24")));
            $this->assertEquals(array('company_id' => 11), $result);

            $expected = $this->createXmlDataSet(dirname(__FILE__) . "/CompanyDataSet/CompanyInsert.xml");
            $actual = new PHPUnit_Extensions_Database_DataSet_QueryDataSet($this->getConnection());
            $actual->addTable('company');
            $this->assertDataSetsEqual($expected, $actual);
        }

        public function testSaveFail(){
            $result = self::$comp_repo->save(new Company(array('company_name' => 'Hunter College', 'type' => 'School',
                                    'join_date' => "2016-08-24")));
            $this->assertEquals(array(), $result);

            $expected = $this->createXmlDataSet(dirname(__FILE__) . "/CompanyDataSet/Seed.xml");
            $actual = new PHPUnit_Extensions_Database_DataSet_QueryDataSet($this->getConnection());
            $actual->addTable('company');
            $this->assertDataSetsEqual($expected, $actual, "Database mismatch");
        }

        public function testRemoveSuccess(){
            $result = self::$comp_repo->remove(new Company(array('company_name' => 'Queens College')));
            $this->assertEquals($result, array('company_name' => 'Queens College'));

            $expected = $this->createXmlDataSet(dirname(__FILE__) . "/CompanyDataSet/CompanyRemove.xml");
            $actual = new PHPUnit_Extensions_Database_DataSet_QueryDataSet($this->getConnection());
            $actual->addTable('company');
            $this->assertDataSetsEqual($expected, $actual);
        }

        public function testRemoveFail(){
            $result = self::$comp_repo->remove(new Company(array('company_name' => 'Queens Collage')));
            $this->assertEquals(array(), $result);

            $expected = $this->createXmlDataSet(dirname(__FILE__) . "/CompanyDataSet/Seed.xml");
            $actual = new PHPUnit_Extensions_Database_DataSet_QueryDataSet($this->getConnection());
            $actual->addTable('company');
            $this->assertDataSetsEqual($expected, $actual);
        }
    }
?>
