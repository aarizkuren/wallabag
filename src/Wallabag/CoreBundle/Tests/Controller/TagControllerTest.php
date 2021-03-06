<?php

namespace Wallabag\CoreBundle\Tests\Controller;

use Wallabag\CoreBundle\Tests\WallabagCoreTestCase;

class TagControllerTest extends WallabagCoreTestCase
{
    public function testList()
    {
        $this->logInAs('admin');
        $client = $this->getClient();

        $client->request('GET', '/tag/list');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }

    public function testAddTagToEntry()
    {
        $this->logInAs('admin');
        $client = $this->getClient();

        $entry = $client->getContainer()
            ->get('doctrine.orm.entity_manager')
            ->getRepository('WallabagCoreBundle:Entry')
            ->findOneByIsArchived(false);

        $crawler = $client->request('GET', '/view/'.$entry->getId());

        $form = $crawler->filter('button[id=tag_save]')->form();

        $data = array(
            'tag[label]' => 'opensource',
        );

        $client->submit($form, $data);
        $this->assertEquals(302, $client->getResponse()->getStatusCode());

        $this->assertEquals(1, count($entry->getTags()));

        # tag already exists and already assigned
        $client->submit($form, $data);
        $this->assertEquals(302, $client->getResponse()->getStatusCode());

        $newEntry = $client->getContainer()
            ->get('doctrine.orm.entity_manager')
            ->getRepository('WallabagCoreBundle:Entry')
            ->findOneById($entry->getId());

        $this->assertEquals(1, count($newEntry->getTags()));

        # tag already exists but still not assigned to this entry
        $data = array(
            'tag[label]' => 'foo',
        );

        $client->submit($form, $data);
        $this->assertEquals(302, $client->getResponse()->getStatusCode());

        $newEntry = $client->getContainer()
            ->get('doctrine.orm.entity_manager')
            ->getRepository('WallabagCoreBundle:Entry')
            ->findOneById($entry->getId());

        $this->assertEquals(2, count($newEntry->getTags()));
    }
}
