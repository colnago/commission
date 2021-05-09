<?php

class CommissionTest extends \Codeception\Test\Unit
{
    /**
     * @var \UnitTester
     */
    protected $tester;

    // tests
    public function testCommissionController()
    {
        $expected = ["0.00", "0.00", "0.30"];
        $this->expectOutputString(implode("\n", $expected) . "\n");
        $output = \app\commands\CommissionController::actionCalculation('input_test.csv');
    }
}