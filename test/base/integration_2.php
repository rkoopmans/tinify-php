<?php
require_once __DIR__ . "/../integration.php";
abstract class IntegrationTestCase_2 extends \PHPUnit\Framework\TestCase {
    static public function setUpBeforeClass(): void {
        Integration::initialize();
    }
}
