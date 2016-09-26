<?php
    require_once(dirname(__FILE__) . '/../../Model/Room.php');

    class RoomTest extends PHPUnit_Framework_TestCase{
        public function testCanBeSaved(){
            $params = array('company_name' => 'Hunter College', 'address' => '695 Park Ave New York NY', 'room_number' =>
                            '606', 'max_cap' => '20');
            $room = new Room($params);

            $this->assertNotEmpty($room->company);
            $this->assertNotEmpty($room->address);
            $this->assertNotEmpty($room->room_number);
            $this->assertNotEmpty($room->max_cap);
            $this->assertEmpty($room->id);
            $this->assertEmpty($room->crowd);
            $this->assertEmpty($room->date);
            $this->assertEmpty($room->time);
            $this->assertTrue($room->canBeSaved());
        }

        public function testCannotBeSaved(){
            $params = array('address' => '695 Park Ave New York NY', 'room_number' => '606', 'max_cap' => '20');
            $room = new Room($params);

            $this->assertNotEmpty($room->address);
            $this->assertNotEmpty($room->room_number);
            $this->assertNotEmpty($room->max_cap);
            $this->assertEmpty($room->company);
            $this->assertEmpty($room->id);
            $this->assertEmpty($room->crowd);
            $this->assertEmpty($room->date);
            $this->assertEmpty($room->time);
            $this->assertFalse($room->canBeSaved());
        }

        public function testCanBeUpdated(){
            $params = array('id' => 1, 'key' => '10294', 'time' => '11:11', 'date' => '2222-22-22');
            $room = new Room($params);

            $this->assertEmpty($room->company);
            $this->assertEmpty($room->address);
            $this->assertEmpty($room->room_number);
            $this->assertEmpty($room->max_cap);
            $this->assertEmpty($room->crowd);
            $this->assertNotEmpty($room->id);
            $this->assertNotEmpty($room->date);
            $this->assertNotEmpty($room->time);
            $this->assertTrue($room->canBeUpdated());
        }

        public function testCannotBeUpdated(){
            $params = array('key' => '10294', 'time' => '11:11', 'date' => '2222-22-22');
            $room = new Room($params);

            $this->assertEmpty($room->company);
            $this->assertEmpty($room->address);
            $this->assertEmpty($room->room_number);
            $this->assertEmpty($room->max_cap);
            $this->assertEmpty($room->crowd);
            $this->assertEmpty($room->id);
            $this->assertNotEmpty($room->date);
            $this->assertNotEmpty($room->time);
            $this->assertFalse($room->canBeUpdated());
        }
    }
?>
