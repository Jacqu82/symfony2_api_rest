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
//            'user',
        ));
        $this->asserter()->assertResponsePropertyEquals($response, 'nickname', 'UnitTester');
        $this->asserter()->assertResponsePropertyEquals(
            $response,
            '_links.self',
            $this->adjustUri('/api/programmers/UnitTester')
        );
        //$this->debugResponse($response);
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
        $this->asserter()->assertResponsePropertyIsArray($response, 'items');
        $this->asserter()->assertResponsePropertyCount($response, 'items', 2);
        $this->asserter()->assertResponsePropertyEquals($response, 'items[0].nickname', 'UnitTester');
    }

    public function testGETProgrammersCollectionPaginated()
    {
        $this->createProgrammer([
            'nickname' => 'willnotmatch',
            'avatarNumber' => 3
        ]);

        for ($i = 0; $i < 25; $i++) {
            $this->createProgrammer(array(
                'nickname' => 'Programmer' . $i,
                'avatarNumber' => mt_rand(1, 5)
            ));
        }

        $response = $this->client->get('/api/programmers?filter=programmer');
        $this->assertEquals(200, $response->getStatusCode());
        $this->asserter()->assertResponsePropertyEquals($response, 'items[5].nickname', 'Programmer5');
        $this->asserter()->assertResponsePropertyEquals($response, 'count', 10);
        $this->asserter()->assertResponsePropertyEquals($response, 'total', 25);
        $this->asserter()->assertResponsePropertyExists($response, '_links.next');

        $nextUrl = $this->asserter()->readResponseProperty($response, '_links.next');

        $response = $this->client->get($nextUrl);
        $this->assertEquals(200, $response->getStatusCode());
        $this->asserter()->assertResponsePropertyEquals($response, 'items[5].nickname', 'Programmer15');
        $this->asserter()->assertResponsePropertyEquals($response, 'count', 10);

        $lastUrl = $this->asserter()->readResponseProperty($response, '_links.last');

        $response = $this->client->get($lastUrl);
        $this->assertEquals(200, $response->getStatusCode());
        $this->asserter()->assertResponsePropertyEquals($response, 'items[4].nickname', 'Programmer24');
        $this->asserter()->assertResponsePropertyDoesNotExist($response, 'items[5].nickname');
        $this->asserter()->assertResponsePropertyEquals($response, 'count', 5);
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

    public function testDELETEProgrammer()
    {
        $this->createProgrammer(array(
            'nickname' => 'UnitTester',
            'avatarNumber' => 3,
        ));

        $response = $this->client->delete('/api/programmers/UnitTester');
        $this->assertEquals(204, $response->getStatusCode());
    }

    public function testPATCHProgrammer()
    {
        $this->createProgrammer(array(
            'nickname' => 'CowboyCoder',
            'avatarNumber' => 1,
            'tagLine' => 'foo'
        ));

        $data = [
            'tagLine' => 'bar'
        ];

        $response = $this->client->patch('/api/programmers/CowboyCoder', [
            'body' => json_encode($data)
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->asserter()->assertResponsePropertyEquals($response, 'tagLine', 'bar');
        $this->asserter()->assertResponsePropertyEquals($response, 'avatarNumber', 1);
    }

    public function testValidationErrors()
    {
        $data = [
            'avatarNumber' => 3,
            'tagLine' => 'a test dev!'
        ];

        $response = $this->client->post('/api/programmers', [
            'body' => json_encode($data)
        ]);

        $this->assertEquals(400, $response->getStatusCode());
        $this->asserter()->assertResponsePropertiesExist($response, [
            'type',
            'title',
            'errors'
        ]);
        $this->asserter()->assertResponsePropertyExists($response, 'errors.nickname');
        $this->asserter()->assertResponsePropertyEquals(
            $response,
            'errors.nickname[0]',
            'Please enter a clever nickname'
        );
        $this->asserter()->assertResponsePropertyDoesNotExist($response, 'errors.avatarNumber');
        //$this->debugResponse($response);
        $this->assertEquals('application/problem+json', $response->getHeader('Content-Type'));

    }

    public function testInvalidJson()
    {
        $invalidJson = <<<EOF
{
    "nickname": "JacaNiePopłaca"
    "avatarNumber": "2",
    "tagLine": "I'm from a test"
}
EOF;

        $response = $this->client->post('/api/programmers', [
            'body' => $invalidJson
        ]);

        //$this->debugResponse($response);
        $this->assertEquals(400, $response->getStatusCode());
        $this->asserter()->assertResponsePropertyContains(
            $response,
            'type',
            'invalid_body_format'
        );
    }

    public function test404Exception()
    {
        //type == what happened?
        $response = $this->client->get('/api/programmers/fake');
        $this->assertEquals(404, $response->getStatusCode());
        $this->assertEquals('application/problem+json', $response->getHeader('Content-Type'));
        $this->asserter()->assertResponsePropertyEquals(
            $response,
            'type',
            'about:blank'
        );
        $this->asserter()->assertResponsePropertyEquals(
            $response,
            'title',
            'Not Found'
        );
        $this->asserter()->assertResponsePropertyEquals(
            $response,
            'detail',
            'No programmer found for username fake!'
        );
    }

}
