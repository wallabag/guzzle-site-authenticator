<?php

namespace BD\GuzzleSiteAuthenticator\Authenticator;

use BD\GuzzleSiteAuthenticator\ExpressionLanguage\AuthenticatorProvider;
use BD\GuzzleSiteAuthenticator\SiteConfig\SiteConfig;
use Http\Client\Common\HttpMethodsClient;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

class LoginFormAuthenticator implements Authenticator
{
    /** @var SiteConfig */
    private $siteConfig;

    public function __construct(SiteConfig $siteConfig)
    {
        // @todo OptionResolver
        $this->siteConfig = $siteConfig;
    }

    /**
     * {@inheritdoc}
     */
    public function login(HttpMethodsClient $httpClient)
    {
        $postFields = [
            $this->siteConfig->getUsernameField() => $this->siteConfig->getUsername(),
            $this->siteConfig->getPasswordField() => $this->siteConfig->getPassword(),
        ] + $this->getExtraFields($httpClient);

        return $httpClient->post(
            $this->siteConfig->getLoginUri(),
            ['Content-Type' => 'application/x-www-form-urlencoded'],
            http_build_query($postFields)
        );
    }

    /**
     * {@inheritdoc}
     */
    public function isLoginRequired(ResponseInterface $response)
    {
        $useInternalErrors = libxml_use_internal_errors(true);

        // need to check for the login dom element ($options['not_logged_in_xpath']) in the HTML
        $doc = new \DOMDocument();
        $doc->loadHTML((string) $response->getBody());

        $xpath = new \DOMXPath($doc);
        $result = $xpath->evaluate($this->siteConfig->getNotLoggedInXpath());
        libxml_use_internal_errors($useInternalErrors);

        if ($result instanceof \DOMNodeList) {
            return $result->length > 0;
        }

        if (is_bool($result)) {
            return $result;
        }

        return false;
    }

    /**
     * Returns extra fields from the configuration.
     * Evaluates any field value that is an expression language string.
     *
     * @param HttpMethodsClient $httpClient
     *
     * @return array
     */
    private function getExtraFields(HttpMethodsClient $httpClient)
    {
        $extraFields = [];

        foreach ($this->siteConfig->getExtraFields() as $fieldName => $fieldValue) {
            if ('@=' === substr($fieldValue, 0, 2)) {
                $expressionLanguage = $this->getExpressionLanguage($httpClient);
                $fieldValue = $expressionLanguage->evaluate(
                    substr($fieldValue, 2),
                    [
                        'config' => $this->siteConfig,
                    ]
                );
            }

            $extraFields[$fieldName] = $fieldValue;
        }

        return $extraFields;
    }

    /**
     * @param HttpMethodsClient $httpClient
     *
     * @return ExpressionLanguage
     */
    private function getExpressionLanguage(HttpMethodsClient $httpClient)
    {
        return new ExpressionLanguage(
            null,
            [new AuthenticatorProvider($httpClient)]
        );
    }
}
