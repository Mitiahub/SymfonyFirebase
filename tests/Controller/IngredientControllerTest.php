<?php

namespace App\Tests\Controller;

use App\Entity\Ingredient;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class IngredientControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $manager;
    private EntityRepository $ingredientRepository;
    private string $path = '/ingredient/';

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->manager = static::getContainer()->get('doctrine')->getManager();
        $this->ingredientRepository = $this->manager->getRepository(Ingredient::class);

        foreach ($this->ingredientRepository->findAll() as $object) {
            $this->manager->remove($object);
        }

        $this->manager->flush();
    }

    public function testIndex(): void
    {
        $this->client->followRedirects();
        $crawler = $this->client->request('GET', $this->path);

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Ingredient index');

        // Use the $crawler to perform additional assertions e.g.
        // self::assertSame('Some text on the page', $crawler->filter('.p')->first());
    }

    public function testNew(): void
    {
        $this->markTestIncomplete();
        $this->client->request('GET', sprintf('%snew', $this->path));

        self::assertResponseStatusCodeSame(200);

        $this->client->submitForm('Save', [
            'ingredient[nom]' => 'Testing',
            'ingredient[quantiteStock]' => 'Testing',
            'ingredient[seuilMinimum]' => 'Testing',
        ]);

        self::assertResponseRedirects($this->path);

        self::assertSame(1, $this->ingredientRepository->count([]));
    }

    public function testShow(): void
    {
        $this->markTestIncomplete();
        $fixture = new Ingredient();
        $fixture->setNom('My Title');
        $fixture->setQuantiteStock('My Title');
        $fixture->setSeuilMinimum('My Title');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Ingredient');

        // Use assertions to check that the properties are properly displayed.
    }

    public function testEdit(): void
    {
        $this->markTestIncomplete();
        $fixture = new Ingredient();
        $fixture->setNom('Value');
        $fixture->setQuantiteStock('Value');
        $fixture->setSeuilMinimum('Value');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s/edit', $this->path, $fixture->getId()));

        $this->client->submitForm('Update', [
            'ingredient[nom]' => 'Something New',
            'ingredient[quantiteStock]' => 'Something New',
            'ingredient[seuilMinimum]' => 'Something New',
        ]);

        self::assertResponseRedirects('/ingredient/');

        $fixture = $this->ingredientRepository->findAll();

        self::assertSame('Something New', $fixture[0]->getNom());
        self::assertSame('Something New', $fixture[0]->getQuantiteStock());
        self::assertSame('Something New', $fixture[0]->getSeuilMinimum());
    }

    public function testRemove(): void
    {
        $this->markTestIncomplete();
        $fixture = new Ingredient();
        $fixture->setNom('Value');
        $fixture->setQuantiteStock('Value');
        $fixture->setSeuilMinimum('Value');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));
        $this->client->submitForm('Delete');

        self::assertResponseRedirects('/ingredient/');
        self::assertSame(0, $this->ingredientRepository->count([]));
    }
}
