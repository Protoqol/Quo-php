<?php

use donatj\MockWebServer\MockWebServer;
use donatj\MockWebServer\Response;
use PHPUnit\Framework\TestCase;
use Protoqol\Quo\Http\QuoPayload;
use Protoqol\Quo\Http\QuoRequest;

/**
 * @group skip-71
 */
class QuoRequestTest extends TestCase
{
    /** @var MockWebServer */
    private static $server;

    public static function setUpBeforeClass(): void
    {
        self::$server = new MockWebServer();
        self::$server->start();
    }

    public static function tearDownAfterClass(): void
    {
        self::$server->stop();
    }

    public function testSendRequest(): void
    {
        $payloadData  = ['test' => 'data'];
        $responseBody = json_encode(['status' => 'success']);

        self::$server->setResponseOfPath('/payload', new Response($responseBody, [], 200));

        $curl    = curl_init();
        $request = new QuoRequest(
            $curl,
            self::$server->getHost(),
            self::$server->getPort()
        );

        $request->setHeaders([CURLOPT_HTTPHEADER => ['X-Custom-Test: verified']]);

        $payload = new QuoPayload($payloadData, 0, null);
        $request->setBody($payload);

        $response = $request->send();

        $this->assertStringContainsString($responseBody, $response);

        $lastRequest = self::$server->getLastRequest();
        $this->assertEquals('POST', $lastRequest->getRequestMethod());

        $input = json_decode($lastRequest->getInput(), true);
        $this->assertIsArray($input);
        $this->assertArrayHasKey('meta', $input);
        $this->assertArrayHasKey('variable', $input['meta']);

        $variable = $input['meta']['variable'];
        $this->assertEquals('array<string>', $variable['var_type']);
        $this->assertEquals('unknown', $variable['name']);
        $this->assertEquals('["test" => "data"]', $variable['value']);
        $this->assertTrue($variable['is_mutable']);
        $this->assertFalse($variable['is_constant']);
        $this->assertTrue($variable['is_expression']);

        $headers = $lastRequest->getHeaders();
        $this->assertEquals('verified', $headers['X-Custom-Test']);

        curl_close($curl);
    }

    public function testErrorCapture(): void
    {
        $curl = curl_init();
        // Use a port that is likely closed
        $request = new QuoRequest($curl, '127.0.0.1', 1);
        $request->send();

        $this->assertNotEmpty($request->getError());
        curl_close($curl);
    }

    public function testSetHeaders(): void
    {
        $curl    = curl_init();
        $request = new QuoRequest(
            $curl,
            'localhost',
            8080
        );

        $this->assertTrue($request->setHeaders([CURLOPT_HTTPHEADER => ['Custom-Header: value']]));
        curl_close($curl);
    }
}
