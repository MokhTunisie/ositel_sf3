<?php
/**
 * Created by PhpStorm.
 * User: HP
 * Date: 03/03/2019
 * Time: 19:17.
 */

namespace AppBundle\Tests\Repository;

use AppBundle\Entity\Transaction;
use AppBundle\Tests\KernelTestRefreshDatabaseTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class TransactionRepositoryTest extends KernelTestCase
{
    use KernelTestRefreshDatabaseTrait;

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $entityManager;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $kernel = self::bootKernel();

        $this->entityManager = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();
        self::runCommand('doctrine:fixtures:load --purge-with-truncate -n --env=test');
    }

    /**
     * @return array
     */
    public function getMonthsProvider()
    {
        $date = new \DateTime('now');
        $year = $date->format('Y');

        return [
            [$year, $date->format('m'), 0, 700],
            [$year, $date->modify('-1 month')->format('m'), 0, 0],
            [$year, $date->modify('+ 2 month')->format('m'), 700, 700],
            [$year, 'wrong month', 0, 0],
            ['wri=ong year', $date->format('m'), 0, 0],
        ];
    }

    /**
     * @throws \Exception
     * @dataProvider getMonthsProvider
     */
    public function testCalculateTreasury($year, $month, $start, $end)
    {
        $monthlyTreasury = $this->entityManager
            ->getRepository(Transaction::class)
            ->calculateTreasury($year, $month);

        $this->assertEquals($start, $monthlyTreasury['start']);
        $this->assertEquals($end, $monthlyTreasury['end']);
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown()
    {
        parent::tearDown();

        $this->entityManager->close();
        $this->entityManager = null; // avoid memory leaks
    }
}
