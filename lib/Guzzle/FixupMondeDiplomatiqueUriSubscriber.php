<?php
/**
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace BD\GuzzleSiteAuthenticator\Guzzle;

use GuzzleHttp\Event\SubscriberInterface;
use GuzzleHttp\Event\CompleteEvent;

/**
 * Fixes url encoding of a parameter guzzle fails with.
 */
class FixupMondeDiplomatiqueUriSubscriber implements SubscriberInterface
{
    public function getEvents()
    {
        return ['complete' => [['fixUri', 500]]];
    }

    public function fixUri(CompleteEvent $event)
    {
        $response = $event->getResponse();

        if (!$response->hasHeader('Location')) {
            return;
        }

        $uri = $response->getHeader('Location');
        if (($badParameter = strstr($uri, 'retour=http://')) === false) {
            return;
        }

        $response->setHeader('Location',  str_replace($badParameter, urlencode($badParameter), $uri));
    }
}
