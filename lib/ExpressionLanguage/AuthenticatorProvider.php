<?php

namespace BD\GuzzleSiteAuthenticator\ExpressionLanguage;

use Exception;
use GuzzleHttp\ClientInterface;
use Symfony\Component\ExpressionLanguage\ExpressionFunction;
use Symfony\Component\ExpressionLanguage\ExpressionFunctionProviderInterface;

class AuthenticatorProvider implements ExpressionFunctionProviderInterface
{
    /**
     * @var ClientInterface
     */
    private $guzzle;

    public function __construct(ClientInterface $guzzle)
    {
        $this->guzzle = $guzzle;
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        $result = [
            $this->getRequestHtmlFunction(),
            $this->getXpathFunction(),
        ];
        if (version_compare(phpversion(), '7.0.0') >= 0) {
            // the function preg_replace has a security issue before version 7 of PHP:
            // the flag "/e" was treating the "replacement" parameter as php code to execute.
            array_push($result, $this->getPregReplaceFunction());
        }
        return $result;
    }

    private function getRequestHtmlFunction()
    {
        return new ExpressionFunction(
            'request_html',
            function () {
                throw new Exception('Not supported');
            },
            function (array $arguments, $uri, array $options = []) {
                return $this->guzzle->get($uri, $options)->getBody();
            }
        );
    }

    private function getPregReplaceFunction()
    {
        return new ExpressionFunction(
            'preg_replace',
            function () {
                throw new Exception('Not supported');
            },
            function (array $arguments, $pattern , $replacement , $subject, int $limit = -1) {
                return preg_replace($pattern, $replacement, $subject, intval(strval($limit)));
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
