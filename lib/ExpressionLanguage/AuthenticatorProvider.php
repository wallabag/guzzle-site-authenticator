<?php

namespace BD\GuzzleSiteAuthenticator\ExpressionLanguage;

use Exception;
use Http\Client\Common\HttpMethodsClient;
use Symfony\Component\ExpressionLanguage\ExpressionFunction;
use Symfony\Component\ExpressionLanguage\ExpressionFunctionProviderInterface;

class AuthenticatorProvider implements ExpressionFunctionProviderInterface
{
    /**
     * @var HttpMethodsClient
     */
    private $httpClient;

    public function __construct(HttpMethodsClient $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return [
            $this->getRequestHtmlFunction(),
            $this->getXpathFunction(),
        ];
    }

    private function getRequestHtmlFunction()
    {
        return new ExpressionFunction(
            'request_html',
            function () {
                throw new Exception('Not supported');
            },
            function (array $arguments, $uri, array $options = []) {
                return (string) $this->httpClient->get($uri, $options)->getBody();
            }
        );
    }

    private function getXpathFunction()
    {
        return new ExpressionFunction(
            'xpath',
            function () {
                throw new Exception('Not supported');
            },
            function (array $arguments, $xpathQuery, $html) {
                $useInternalErrors = libxml_use_internal_errors(true);

                $doc = new \DOMDocument();
                $doc->loadHTML($html, LIBXML_NOCDATA | LIBXML_NOWARNING | LIBXML_NOERROR);

                $xpath = new \DOMXPath($doc);
                $domNodeList = $xpath->query($xpathQuery);
                $domNode = $domNodeList->item(0);

                libxml_use_internal_errors($useInternalErrors);

                return $domNode->attributes->getNamedItem('value')->nodeValue;
            }
        );
    }
}
