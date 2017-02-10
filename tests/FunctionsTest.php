<?php declare(strict_types=1);

namespace Consatan\Support\Tests;

class FunctionsTest extends \PHPUnit\Framework\TestCase
{
    public function testArrayToXML()
    {
        $arr = ['child' => [
            ['name' => 'chopin', 'age' => 11],
            ['name' => 'ngo', 'age' => 10],
        ]];
        $xml = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<xml><child name="chopin" age="11"/><child name="ngo" age="10"/></xml>

XML;
        $this->assertEquals($xml, \Consatan\Support\array2xml($arr)->asXML());
        $this->assertEquals($xml, \Consatan\Support\array2xml($arr, ' ')->asXML());
    }
}
