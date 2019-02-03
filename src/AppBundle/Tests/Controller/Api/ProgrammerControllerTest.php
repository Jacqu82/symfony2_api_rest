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
        $this->assertStringEndsWith('/api/programmers/ObjectOrienter', $response->getHeader('Location'));
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
        $this->asserter()->assertResponsePropertiesExist($response, array(
            'nickname',
            'avatarNumber',
            'tagLine',
            'powerLevel',
            'user',
        ));
        $this->asserter()->assertResponsePropertyEquals($response, 'nickname', 'UnitTester');
//        $data = $response->json();
//        $this->assertEquals(array(
//            'nickname',
//            'avatarNumber',
//            'tagLine',
//            'powerLevel',
//            'user',
//        ), array_keys($data));
    }

    public function testGETProgrammersCollection()
    {
        $this->createProgrammer(array(
            'nickname' => 'UnitTester',
            'avatarNumber' => 3,
        ));
        $this->createProgrammer(array(
            'nickname' => 'CowboyCoder',
            'avatarNumber' => 1,
        ));

        $response = $this->client->get('/api/programmers');
        $this->assertEquals(200, $response->getStatusCode());
        $this->asserter()->assertResponsePropertyIsArray($response, 'programmers');
        $this->asserter()->assertResponsePropertyCount($response, 'programmers', 2);
        $this->asserter()->assertResponsePropertyEquals($response, 'programmers[0].nickname', 'UnitTester');
    }

    public function testPUTProgrammer()
    {
        $this->createProgrammer(array(
            'nickname' => 'CowboyCoder',
            'avatarNumber' => 1,
            'tagLine' => 'foo'
        ));

        $data = [
            'nickname' => 'CowgirlCoder',
            'avatarNumber' => 2,
            'tagLine' => 'bar'
        ];

        $response = $this->client->put('/api/programmers/CowboyCoder', [
            'body' => json_encode($data)
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->asserter()->assertResponsePropertyEquals($response, 'avatarNumber', 2);
        $this->asserter()->assertResponsePropertyEquals($response, 'nickname', 'CowboyCoder');
    }
}
