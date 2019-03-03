<?php
/**
 * Created by PhpStorm.
 * User: HP
 * Date: 28/02/2019
 * Time: 12:56.
 */

namespace AppBundle\Fixtures;

use AppBundle\Entity\Category;
use AppBundle\Entity\Tag;
use AppBundle\Entity\Transaction;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        // create 20 products! Bam!
        for ($i = 1; $i < 6; ++$i) {
            $category = new Category();
            $category->setTitle('Category '.$i);
            $manager->persist($category);
        }

        for ($i = 1; $i < 6; ++$i) {
            $tag = new Tag();
            $tag->setName('Tag '.$i);
            $manager->persist($tag);
        }

        $manager->flush();

        for ($i = 1; $i < 11; ++$i) {
            $transaction = new Transaction();
            $transaction->setTitle('Transaction '.$i);
            $transaction->setDescription('Transaction '.$i.' Description');
            $transaction->setAmount($i * 100);
            $transaction->setIsInput($i <= 7 ? true : false);
            $transaction->setIsValid(in_array($i, [2, 8], true) ? false : true);
            $transaction->setCategory($manager->find(Category::class, $i <= 5 ? $i : ($i - 5)));

            if ($i < 5) {
                $transaction->addTag($manager->find(Tag::class, $i));
                $transaction->addTag($manager->find(Tag::class, $i + 1));
            } elseif ($i > 6) {
                $transaction->addTag($manager->find(Tag::class, $i - 5));
                $transaction->addTag($manager->find(Tag::class, $i - 6));
            }
            $manager->persist($transaction);
        }

        $manager->flush();
    }
}
