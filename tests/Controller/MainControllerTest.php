<?php


namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class MainControllerTest extends WebTestCase
{
    public function testIndexResponse()
    {
        $client = static::createClient();
        $client->request("GET", "/");
        $response = $client->getResponse();
        $contentType = $response->headers->get("Content-Type");
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("application/json", $contentType);
    }

    public function testIndexContent()
    {
        $client = static::createClient();
        $client->request("GET", "/");
        $response = $client->getResponse();
        $content = $response->getContent();
        $jsonContent = json_decode($content, true);
        $this->assertEquals("array", gettype($jsonContent["routes"]));
        $this->assertEquals(0, $this->getNumAssertions());
    }

    public function testParsedAgendaHeaders()
    {
        $client = static::createClient();
        $client->request("GET", "/agenda");
        $response = $client->getResponse();
        $headers = $response->headers->all();
        $this->assertEquals("attachment; filename=agenda.ics", $headers["content-disposition"][0]);
        $this->assertEquals("text/html; charset=UTF-8", $headers["content-type"][0]);
    }

    public function testOriginalAgendaContentType()
    {
        $client = static::createClient();
        $client->request("GET", "/original");
        $response = $client->getResponse();
        $contentType = $response->headers->get("content-type");
        $this->assertEquals("text/html; charset=UTF-8", $contentType);
    }

    public function testRawAgendaContentType()
    {
        $client = static::createClient();
        $client->request("GET", "/raw");
        $response = $client->getResponse();
        $contentType = $response->headers->get("content-type");
        $this->assertEquals("text/html; charset=UTF-8", $contentType);
    }
}
