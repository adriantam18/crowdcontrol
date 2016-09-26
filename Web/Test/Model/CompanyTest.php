<?php
    require_once(dirname(__FILE__) . '/../../Model/Company.php');

    class CompanyTest extends PHPUnit_Framework_TestCase{
        public function testCanBeSaved(){
            $params = array('company_name' => 'Hunter College', 'type' => 'School', 'join_date' => '0000-00-00');
            $company = new Company($params);

            $this->assertNotEmpty($company->company_name);
            $this->assertNotEmpty($company->type);
            $this->assertNotEmpty($company->join_date);
            $this->assertTrue($company->canBeSaved());
        }

        public function testCannotBeSaved(){
            $params = array('type' => 'School', 'join_date' => '0000-00-00');
            $company = new Company($params);

            $this->assertEmpty($company->company_name);
            $this->assertNotEmpty($company->type);
            $this->assertNotEmpty($company->join_date);
            $this->assertFalse($company->canBeSaved());
        }
    }
?>
