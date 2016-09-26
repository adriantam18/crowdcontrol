<?php
    require_once(dirname(__FILE__) . '/../../Model/Branch.php');

    class BranchTest extends PHPUnit_Framework_TestCase{
        public function testCanBeSaved(){
            $params = array('company_name' => 'Hunter College', 'address' => '695 Park Ave New York NY', 'zipcode' =>
                            '10065', 'open_hours' => '11:11', 'close_hours' => '22:22');
            $branch = new Branch($params);

            $this->assertNotEmpty($branch->company);
            $this->assertNotEmpty($branch->address);
            $this->assertNotEmpty($branch->zipcode);
            $this->assertNotEmpty($branch->open_hours);
            $this->assertNotEmpty($branch->close_hours);
            $this->assertEmpty($branch->id);
            $this->assertEmpty($branch->lat);
            $this->assertEmpty($branch->lng);
            $this->assertTrue($branch->canBeSaved());
        }

        public function testCannotBeSaved(){
            $params = array('company_name' => 'Hunter College', 'zipcode' => '10065', 'open_hours' => '11:11',
                            'close_hours' => '22:22', 'lat' => '11.2222', 'lng' => '22.1111');
            $branch = new Branch($params);

            $this->assertNotEmpty($branch->company);
            $this->assertNotEmpty($branch->zipcode);
            $this->assertNotEmpty($branch->open_hours);
            $this->assertNotEmpty($branch->close_hours);
            $this->assertNotEmpty($branch->lat);
            $this->assertNotEmpty($branch->lng);
            $this->assertEmpty($branch->address);
            $this->assertEmpty($branch->id);
            $this->assertFalse($branch->canBeSaved());
        }

        public function testCanBeUpdated(){
            $params = array('id' => 1, 'company_name' => 'Hunter College', 'zipcode' => '10065', 'open_hours' => '11:11',
                            'close_hours' => '22:22', 'lat' => '11.2222', 'lng' => '22.1111');
            $branch = new Branch($params);

            $this->assertNotEmpty($branch->company);
            $this->assertNotEmpty($branch->zipcode);
            $this->assertNotEmpty($branch->open_hours);
            $this->assertNotEmpty($branch->close_hours);
            $this->assertNotEmpty($branch->lat);
            $this->assertNotEmpty($branch->lng);
            $this->assertNotEmpty($branch->id);
            $this->assertEmpty($branch->address);
            $this->assertTrue($branch->canBeUpdated());
        }

        public function testCannotBeUpdated(){
            $params = array('company_name' => 'Hunter College', 'zipcode' => '10065', 'open_hours' => '11:11',
                            'close_hours' => '22:22', 'lat' => '11.2222', 'lng' => '22.1111');
            $branch = new Branch($params);

            $this->assertNotEmpty($branch->company);
            $this->assertNotEmpty($branch->zipcode);
            $this->assertNotEmpty($branch->open_hours);
            $this->assertNotEmpty($branch->close_hours);
            $this->assertNotEmpty($branch->lat);
            $this->assertNotEmpty($branch->lng);
            $this->assertEmpty($branch->id);
            $this->assertEmpty($branch->address);
            $this->assertFalse($branch->canBeUpdated());
        }
    }
?>
