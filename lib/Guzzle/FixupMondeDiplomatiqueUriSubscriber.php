<?php

namespace BD\GuzzleSiteAuthenticator\Guzzle;

use GuzzleHttp\Event\CompleteEvent;
use GuzzleHttp\Event\SubscriberInterface;

/**
 * Fixes url encoding of a parameter guzzle fails with.
 */
class FixupMondeDiplomatiqueUriSubscriber implements SubscriberInterface
{
    /**
     * {@inheritdoc}
     */
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

        $response->setHeader('Location', str_replace($badParameter, urlencode($badParameter), $uri));
    }
}
