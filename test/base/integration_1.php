<?php
require_once __DIR__ . "/../integration.php";
abstract class IntegrationTestCase_1 extends \PHPUnit\Framework\TestCase {
    static public function setUpBeforeClass() {
        Integration::initialize();
    }
}
