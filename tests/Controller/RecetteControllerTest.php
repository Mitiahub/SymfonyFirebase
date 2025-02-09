<?php

namespace App\Tests\Controller;

use App\Entity\Recette;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class RecetteControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $manager;
    private EntityRepository $recetteRepository;
    private string $path = '/recette/';

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->manager = static::getContainer()->get('doctrine')->getManager();
        $this->recetteRepository = $this->manager->getRepository(Recette::class);

        foreach ($this->recetteRepository->findAll() as $object) {
            $this->manager->remove($object);
        }

        $this->manager->flush();
    }

    public function testIndex(): void
    {
        $this->client->followRedirects();
        $crawler = $this->client->request('GET', $this->path);

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Recette index');

        // Use the $crawler to perform additional assertions e.g.
        // self::assertSame('Some text on the page', $crawler->filter('.p')->first());
    }

    public function testNew(): void
    {
        $this->markTestIncomplete();
        $this->client->request('GET', sprintf('%snew', $this->path));

        self::assertResponseStatusCodeSame(200);

        $this->client->submitForm('Save', [
            'recette[nom]' => 'Testing',
            'recette[tempsCuisson]' => 'Testing',
            'recette[description]' => 'Testing',
            'recette[imageUrl]' => 'Testing',
        ]);

        self::assertResponseRedirects($this->path);

        self::assertSame(1, $this->recetteRepository->count([]));
    }

    public function testShow(): void
    {
        $this->markTestIncomplete();
        $fixture = new Recette();
        $fixture->setNom('My Title');
        $fixture->setTempsCuisson('My Title');
        $fixture->setDescription('My Title');
        $fixture->setImageUrl('My Title');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Recette');

        // Use assertions to check that the properties are properly displayed.
    }

    public function testEdit(): void
    {
        $this->markTestIncomplete();
        $fixture = new Recette();
        $fixture->setNom('Value');
        $fixture->setTempsCuisson('Value');
        $fixture->setDescription('Value');
        $fixture->setImageUrl('Value');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s/edit', $this->path, $fixture->getId()));

        $this->client->submitForm('Update', [
            'recette[nom]' => 'Something New',
            'recette[tempsCuisson]' => 'Something New',
            'recette[description]' => 'Something New',
            'recette[imageUrl]' => 'Something New',
        ]);

        self::assertResponseRedirects('/recette/');

        $fixture = $this->recetteRepository->findAll();

        self::assertSame('Something New', $fixture[0]->getNom());
        self::assertSame('Something New', $fixture[0]->getTempsCuisson());
        self::assertSame('Something New', $fixture[0]->getDescription());
        self::assertSame('Something New', $fixture[0]->getImageUrl());
    }

    public function testRemove(): void
    {
        $this->markTestIncomplete();
        $fixture = new Recette();
        $fixture->setNom('Value');
        $fixture->setTempsCuisson('Value');
        $fixture->setDescription('Value');
        $fixture->setImageUrl('Value');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));
        $this->client->submitForm('Delete');

        self::assertResponseRedirects('/recette/');
        self::assertSame(0, $this->recetteRepository->count([]));
    }
}
