<?php

namespace AppBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use AppBundle\Tests\WebTestRefreshDatabaseTrait;

class TransactionControllerTest extends WebTestCase
{
    use WebTestRefreshDatabaseTrait;

    protected function setUp()
    {
        parent::setUp(); // TODO: Change the autogenerated stub
        self::runCommand('doctrine:fixtures:load --purge-with-truncate -n --env=test');
    }

    public function testFilter()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/transactions/');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertEquals(5, $crawler->filter('tbody>tr')->count(), 'Invalid number of Transactions');
        $form = $crawler->selectButton('Filtrer')->form([
            'filterField' => 'title',
            'filterValue' => '10',
        ]);
        $crawler = $client->submit($form);
        $this->assertEquals(1, $crawler->filter('tbody>tr')->count(), 'Missing element : td:contains(Transaction 10)');
    }

    public function testCompleteScenario()
    {
        // Create a new client to browse the application
        $client = static::createClient();

        // Create a new entry in the database
        $crawler = $client->request('GET', '/transactions/');
        $this->assertEquals(200, $client->getResponse()->getStatusCode(), 'Unexpected HTTP status code for GET /transactions/');
        $crawler = $client->click($crawler->selectLink('Creer un nouveau élément')->link());
        $form = $crawler->selectButton('Sauvgarder')->form([
            'appbundle_transaction[title]' => 'Transaction title',
            'appbundle_transaction[amount]' => '1200',
            'appbundle_transaction[description]' => 'Transaction description',
            'appbundle_transaction[category]' => 1,
            'appbundle_transaction[tags]' => ['1', '3'],
            'appbundle_transaction[isInput]' => 1,
            'appbundle_transaction[isValid]' => 1,
        ]);

        $client->submit($form);
        $crawler = $client->followRedirect();

        // Check data in the show view
        $this->assertGreaterThan(0, $crawler->filter('td:contains("Transaction title")')->count(), 'Missing element td:contains("Transaction title")');

        // Edit the entity
        $crawler = $client->click($crawler->selectLink('Modifier')->link());

        $form = $crawler->selectButton('Sauvgarder')->form([
            'appbundle_transaction[title]' => 'Transaction title updated',
            'appbundle_transaction[amount]' => '1100',
            'appbundle_transaction[description]' => 'Transaction description updated',
            'appbundle_transaction[category]' => 2,
            'appbundle_transaction[tags]' => ['2', '4'],
        ]);
        $form['appbundle_transaction[isValid]']->untick();
        $form['appbundle_transaction[isInput]']->untick();

        $client->submit($form);
        $crawler = $client->followRedirect();
//        dump($crawler); die();
        // Check the element contains an attribute with value equals "Foo"
        $this->assertGreaterThan(0, $crawler->filter('td:contains("Transaction title updated")')->count(), 'Missing element td:contains("Transaction title updated")');

        // Delete the entity
        $client->submit($crawler->selectButton('Delete')->form());
        $crawler = $client->followRedirect();

        // Check the entity has been delete on the list
        $this->assertNotRegExp('/Transaction title updated/', $client->getResponse()->getContent());
    }
}
