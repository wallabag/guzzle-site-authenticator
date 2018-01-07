<?php
/**
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace BD\GuzzleSiteAuthenticator\Guzzle;

use GuzzleHttp\Event\BeforeEvent;
use GuzzleHttp\Event\RequestEvents;
use GuzzleHttp\Event\SubscriberInterface;

/**
 * Redirect lemonde.fr links when authentication is present
 */
class RedirectLemondeSubscriber implements SubscriberInterface
{
    /**
     * {@inheritdoc}
     */
    public function getEvents()
    {
        return ['before' => [['rewriteURL', RequestEvents::REDIRECT_RESPONSE]]];
    }

    public function rewriteURL(BeforeEvent $event)
    {
        $request = $event->getRequest();
        $url = parse_url($request->getUrl());
        $cookie = $request->getHeader('cookie');

        if ($url['host'] == 'www.lemonde.fr' && !empty($cookie))
        {
            $request->setHost('abonnes.lemonde.fr');
        }
    }
}
