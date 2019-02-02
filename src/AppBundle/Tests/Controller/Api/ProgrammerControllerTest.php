<?php

namespace AppBundle\Tests\Controller\Api;

use AppBundle\Test\ApiTestCase;

class ProgrammerControllerTest extends ApiTestCase
{
    public function setUp()
    {
        parent::setup();

        $this->createUser('weaverryan');
    }

    public function testPOST()
    {
        $data = [
            'nickname' => 'ObjectOrienter',
            'avatarNumber' => 3,
            'tagLine' => 'a test dev!'
        ];

        $response = $this->client->post('/api/programmers', [
            'body' => json_encode($data)
        ]);

        $this->assertEquals(201, $response->getStatusCode());
        $this->assertEquals('/api/programmers/ObjectOrienter', $response->getHeader('Location'));
        $finishedData = json_decode($response->getBody(), true);
        $this->assertArrayHasKey('nickname', $finishedData);
        $this->assertEquals('ObjectOrienter', $data['nickname']);
    }

    public function testGETProgrammer()
    {
        $this->createProgrammer(array(
            'nickname' => 'UnitTester',
            'avatarNumber' => 3,
        ));
        $response = $this->client->get('/api/programmers/UnitTester');
        $this->assertEquals(200, $response->getStatusCode());
        $data = $response->json();
        $this->assertEquals(array(
            'nickname',
            'avatarNumber',
            'tagLine',
            'powerLevel',
            'user',
        ), array_keys($data));
    }
}
