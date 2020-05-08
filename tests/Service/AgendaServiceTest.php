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

    public function testAddEvents()
    {
        $service = $this->getService();
        $fakeEvent = [[
            "DTSTAMP" => "DTSTAMP",
            "DTSTART" => "DTSTART",
            "DTEND" => "DTEND",
            "SUMMARY" => "Summary:Test",
            "LOCATION" => "Location",
            "DESCRIPTION" => "Description",
            "UID" => "UID",
        ]];
        $formattedEvent = "BEGIN:VEVENT\n";
        $formattedEvent .= "DTSTAMP:DTSTAMP\n";
        $formattedEvent .= "DTSTART:DTSTART\n";
        $formattedEvent .= "DTEND:DTEND\n";
        $formattedEvent .= "SUMMARY:Summary - Test\n";
        $formattedEvent .= "LOCATION:Location\n";
        $formattedEvent .= "DESCRIPTION:Description\n";
        $formattedEvent .= "UID:UID\n";
        $formattedEvent .= "END:VEVENT\n";
        $this->assertEquals($formattedEvent, $service->addEvents($fakeEvent));
    }

    public function testAddEventsEmpty()
    {
        $service = $this->getService();
        $this->assertEquals("", $service->addEvents([]));
    }

    public function testGetGroups()
    {
        $service = $this->getService();
        $this->assertEquals(["3172", "6467"], $service->getGroups("3172,6467"));
        $this->assertEquals($service::GROUPS, $service->getGroups("all"));
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

    public function testFormatDescriptionUnknownNumbers()
    {
        $service = $this->getService();
        $rawDescription = "\\n1584368041800\\n1I\\n2I\\n3I-IN10\\nAURION\\nLEFEBVRE P.\\n(Expor
 ted :29/04/2020 17:59)\\n";
        $correctDescription = $service->formatDescription($rawDescription);
        $this->assertStringNotContainsString("1584368041800", $correctDescription);
    }

    public function testFormatLocation()
    {
        $service = $this->getService();
        $rawLocation = "4451\,4307+\,4451-2";
        $correctLocation = $service->formatLocation($rawLocation);
        $this->assertEquals("4451\, 4307\, 4451-2", $correctLocation);
    }
}
