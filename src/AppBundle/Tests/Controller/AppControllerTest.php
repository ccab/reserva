<?php

namespace AppBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class DefaultControllerTest extends WebTestCase
{
    public function testIndex()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/login');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertContains('Acceso seguro', $crawler->text());
    }


    /**
     * The application contains a lot of secure URLs which shouldn't be
     * publicly accessible. This tests ensures that whenever a user tries to
     * access one of those pages, a redirection to the login form is performed.
     *
     * @dataProvider getSecureUrls
     */
    public function testSecureUrls($url)
    {
        $client = self::createClient();
        $client->request('GET', $url);

        $this->assertTrue($client->getResponse()->isRedirect());

        $this->assertEquals(
            'http://localhost/login',
            $client->getResponse()->getTargetUrl(),
            sprintf('The %s secure URL redirects to the login form.', $url)
        );
    }

    public function getSecureUrls()
    {
        return array(
            array('/admin/'),
            array('/reservar/semana'),
        );
    }

}
