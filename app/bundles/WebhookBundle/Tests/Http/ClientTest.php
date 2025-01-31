<?php

namespace Mautic\WebhookBundle\Tests\Http;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Mautic\CoreBundle\Helper\CoreParametersHelper;
use Mautic\WebhookBundle\Http\Client;
use PHPUnit\Framework\TestCase;

class ClientTest extends TestCase
{
    public function testPost(): void
    {
        $method  = 'POST';
        $url     = 'url';
        $payload = ['payload'];
        $siteUrl = 'siteUrl';
        $headers = [
            'Content-Type'      => 'application/json',
            'X-Origin-Base-URL' => $siteUrl,
        ];

        $response = new Response(); // here too

        $parametersMock     = $this->createMock(CoreParametersHelper::class);
        $httpClientMock     = $this->createMock(GuzzleClient::class);

        $parametersMock->expects($this->once())
            ->method('get')
            ->with('site_url')
            ->willReturn($siteUrl);

        $httpClientMock->expects($this->once())
            ->method('sendRequest')
            ->with($this->callback(function (Request $request) use ($method, $url, $headers, $payload) {
                $this->assertSame($method, $request->getMethod());
                $this->assertSame($url, $request->getUri()->getPath());

                foreach ($headers as $headerName => $headerValue) {
                    $header = $request->getHeader($headerName);
                    $this->assertSame($headerValue, $header[0]);
                }

                $this->assertSame(json_encode($payload), (string) $request->getBody());

                return true;
            }))
            ->willReturn($response);

        $client = new Client($parametersMock, $httpClientMock);

        $this->assertEquals($response, $client->post($url, $payload));
    }
}
