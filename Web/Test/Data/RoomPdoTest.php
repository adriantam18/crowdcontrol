<?php
    require_once(dirname(__FILE__) . '/../../Data/RoomRepository.php');
    require_once(dirname(__FILE__) . '/../../Data/RoomPdoRepository.php');
    require_once(dirname(__FILE__) . '/../../Model/Room.php');

    class RoomPdoTest extends PHPUnit_Extensions_Database_TestCase{
        static private $pdo = null;
        private $conn = null;
        static private $room_repo = null;

        public static function setUpBeforeClass(){
            self::$pdo = new PDO($GLOBALS['DB_DSN'], $GLOBALS['DB_USER'], $GLOBALS['DB_PASS']);
            self::$room_repo = new RoomPdoRepository(self::$pdo);
        }

        public static function tearDownAfterClass(){
            self::$pdo = null;
            self::$room_repo = null;
        }

        final public function getConnection(){
            if($this->conn == null){
                if(self::$pdo == null){
                    self::$pdo = new PDO($GLOBALS['DB_DSN'], $GLOBALS['DB_USER'], $GLOBALS['DB_PASS']);
                    self::$room_repo = new RoomPdoRepository(self::$pdo);
                }
                $this->conn = $this->createDefaultDBConnection(self::$pdo, $GLOBALS['DB_DBNAME']);
            }

            return $this->conn;
        }

        protected function getDataSet(){
            return $this->createXMLDataSet(dirname(__FILE__) . '/RoomDataSet/Seed.xml');
        }

        public function testFindByIdSuccess(){
            $result = self::$room_repo->findById(9);
            $expected = array(array('room_id' => 9, 'branch_id' => 3, 'room_number' => '606', 'crowd' => '33', 'time' =>
                                '07:14 PM', 'date' => '2016-08-23'));
            $this->assertEquals($expected, $result);
        }

        public function testFindByIdWithInvalidId(){
            $result = self::$room_repo->findById(100);
            $expected = array();
            $this->assertEquals($expected, $result);
        }

        public function testFindByBranchSuccess(){
            //All rooms from a branch
            $result = self::$room_repo->findByBranch(1);
            $room1 = array('room_id' => 1, 'branch_id' => 1, 'room_number' => '1000A', 'crowd' => '62', 'time' => '12:00 AM',
                        'date' => '0000-00-00');
            $room2 = array('room_id' => 4, 'branch_id' => 1, 'room_number' => '1000D', 'crowd' => '100', 'time' => '12:00 AM',
                        'date' => '0000-00-00');
            $room3 = array('room_id' => 3, 'branch_id' => 1, 'room_number' => '1000J', 'crowd' => '100', 'time' => '06:40 AM',
                        'date' => '2015-12-10');
            $expected = array($room1, $room2, $room3);
            $this->assertEquals($expected, $result);

            //All rooms from a branch that are less than a given crowd percentage
            $result = self::$room_repo->findByBranch(1, 70);
            $room1 = array('room_id' => 1, 'branch_id' => 1, 'room_number' => '1000A', 'crowd' => '62', 'time' => '12:00 AM',
                        'date' => '0000-00-00');
            $expected = array($room1);
            $this->assertEquals($expected, $result);
        }

        public function testFindByBranchFail(){
            //There are no rooms from a branch that are less than a given crowd percentage
            $result = self::$room_repo->findByBranch(1, 50);
            $expected = array();
            $this->assertEquals($expected, $result);

            //Invalid branch id
            $result = self::$room_repo->findByBranch(100);
            $this->assertEquals($expected, $result);
        }

        public function testFindByCompanyAndAddressSuccess(){
            $result = self::$room_repo->findByCompanyAndAddress('Starbucks Coffee', '2034 Sunrise Highway Valley Stream NY');
            $room1 = array('room_id' => 8, 'branch_id' => 3, 'room_number' => '505', 'crowd' => '15', 'time' => '12:16 PM',
                        'date' => '2016-08-22');
            $room2 = array('room_id' => 9, 'branch_id' => 3, 'room_number' => '606', 'crowd' => '33', 'time' => '07:14 PM',
                        'date' => '2016-08-23');
            $expected = array($room1, $room2);
            $this->assertEquals($expected, $result);
        }

        public function testFindByCompanyAndAddressFail(){
            $result = self::$room_repo->findByCompanyAndAddress('Starbucks Coffee', '500 Sunrise Highway Valley Stream NY');
            $expected = array();
            $this->assertEquals($expected, $result);
        }

        public function testSaveSuccess(){
            $this->markTestSkipped('Can\'t predict what they password and hash will be');
            $result = self::$room_repo->save(new Room(array('company_name' => 'Starbucks Coffee', 'address' => '695 Park Ave New York NY',
                                    'room_number' => '606', 'max_cap' => '40')));
            $this->assertEquals(array('room_id' => 10, 'key' => ''), $result, "Result failed");

            $expected = $this->createXmlDataSet(dirname(__FILE__) . "/RoomDataSet/RoomInsert.xml");
            $actual = new PHPUnit_Extensions_Database_DataSet_QueryDataSet($this->getConnection());
            $actual->addTable('room');
            $this->assertDataSetsEqual($expected, $actual, "Database mismatch");
        }

        public function testSaveFail(){
            $this->markTestSkipped('Can\'t predict what they password and hash will be');
            $result = self::$room_repo->save(new Room(array('company_name' => 'Starbucks Coffee', 'address' =>
                                    '2034 Sunrise Highway Valley Stream NY', 'room_number' => '606', 'max_cap' => '40')));
            $this->assertEquals(array(), $result, "Result failed");

            $expected = $this->createXmlDataSet(dirname(__FILE__) . "/RoomDataSet/Seed.xml");
            $actual = new PHPUnit_Extensions_Database_DataSet_QueryDataSet($this->getConnection());
            $actual->addTable('room');
            $this->assertDataSetsEqual($expected, $actual, "Database mismatch");
        }

        public function testUpdateAddSuccess(){
            $result = self::$room_repo->update(new Room(array('id' => 9, 'key' => '4lD2pJ', 'crowd' => 15)));
            $this->assertEquals(array('room_id' => 9), $result);

            $expected = $this->createXmlDataSet(dirname(__FILE__) . "/RoomDataSet/RoomUpdateAdd.xml");
            $actual = new PHPUnit_Extensions_Database_DataSet_QueryDataSet($this->getConnection());
            $actual->addTable('room');
            $this->assertDataSetsEqual($expected, $actual);
        }

        public function testUpdateSubtractSuccess(){
            $result = self::$room_repo->update(new Room(array('id' => 9, 'key' => '4lD2pJ', 'crowd' => -5)));
            $this->assertEquals(array('room_id' => 9), $result);

            $expected = $this->createXmlDataSet(dirname(__FILE__) . "/RoomDataSet/RoomUpdateSubtract.xml");
            $actual = new PHPUnit_Extensions_Database_DataSet_QueryDataSet($this->getConnection());
            $actual->addTable('room');
            $this->assertDataSetsEqual($expected, $actual);
        }

        public function testUpdateResetSuccess(){
            $result = self::$room_repo->update(new Room(array('id' => 9, 'key' => '4lD2pJ')));
            $this->assertEquals(array('room_id' => 9), $result);

            $expected = $this->createXmlDataSet(dirname(__FILE__) . "/RoomDataSet/RoomUpdateReset.xml");
            $actual = new PHPUnit_Extensions_Database_DataSet_QueryDataSet($this->getConnection());
            $actual->addTable('room');
            $this->assertDataSetsEqual($expected, $actual);
        }

        public function testUpdateFail(){
            $result = self::$room_repo->update(new Room(array('id' => 9, 'key' => '4lD2p')));
            $this->assertEquals(array(), $result);

            $expected = $this->createXmlDataSet(dirname(__FILE__) . "/RoomDataSet/Seed.xml");
            $actual = new PHPUnit_Extensions_Database_DataSet_QueryDataSet($this->getConnection());
            $actual->addTable('room');
            $this->assertDataSetsEqual($expected, $actual);
        }

        public function testRemoveSuccess(){
            $result = self::$room_repo->remove(new Room(array('id' => 9, 'key' => '4lD2pJ')));
            $this->assertEquals(array('room_id' => 9), $result);

            $expected = $this->createXmlDataSet(dirname(__FILE__) . "/RoomDataSet/RoomRemove.xml");
            $actual = new PHPUnit_Extensions_Database_DataSet_QueryDataSet($this->getConnection());
            $actual->addTable('room');
            $this->assertDataSetsEqual($expected, $actual);
        }

        public function testRemoveFail(){
            $result = self::$room_repo->remove(new Room(array('id' => 9, 'key' => '4lD2p')));
            $this->assertEquals(array(), $result);

            $expected = $this->createXmlDataSet(dirname(__FILE__) . "/RoomDataSet/Seed.xml");
            $actual = new PHPUnit_Extensions_Database_DataSet_QueryDataSet($this->getConnection());
            $actual->addTable('room');
            $this->assertDataSetsEqual($expected, $actual);
        }
    }
?>
