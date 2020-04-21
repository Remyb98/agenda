<?php


namespace App\Tests\Service;

use App\Service\AgendaService;
use PHPUnit\Framework\TestCase;

class AgendaServiceTest extends TestCase
{
    private function getService(): AgendaService
    {
        return new AgendaService();
    }

    public function testChangeEventSummary()
    {
        $service = $this->getService();
        $rawSummaryTDR = "3I-IN11:TDR";
        $rawSummaryTPs = "3I-IN11:TPs";
        $correctSummaryTDR = $service->changeEventSummary($rawSummaryTDR);
        $correctSummaryTPs = $service->changeEventSummary($rawSummaryTPs);
        $this->assertEquals("Infographie 3D - TDR", $correctSummaryTDR);
        $this->assertEquals("Infographie 3D - TPs", $correctSummaryTPs);
    }

    public function testChangeEventSummaryDoesNotExist()
    {
        $service = $this->getService();
        $rawSummary = "undefined:undefined";
        $correctSummary = $service->changeEventSummary($rawSummary);
        $this->assertEquals("undefined - undefined", $correctSummary);
    }

    public function testFormatEventName()
    {
        $service = $this->getService();
        $rawEventName = "3I-IN11";
        $correctEventName = $service->formatEventName($rawEventName);
        $this->assertEquals("Infographie 3D", $correctEventName);
    }

    public function testFormatEventNameDoesNotExist()
    {
        $service = $this->getService();
        $rawEventName = "undefined";
        $correctEventName = $service->formatEventName($rawEventName);
        $this->assertEquals("undefined", $correctEventName);
    }

    public function testFormatDescription()
    {
        $service = $this->getService();
        $rawDescription = "\\n3I-IN12\\nAURION\\nRAYNAL B.\\n(Exported :21/04/2020 15:29)\\n";
        $correctDescription = $service->formatDescription($rawDescription);
        $this->assertStringNotContainsString("AURION", $correctDescription);
        $this->assertStringContainsString("https://agenda.remybarberet.fr", $correctDescription);
    }

    public function testFormatDescriptionInvalid()
    {
        $service = $this->getService();
        $rawDescription = "Invalid description";
        $correctDescription = $service->formatDescription($rawDescription);
        $this->assertEquals($correctDescription, $rawDescription);
    }
}
