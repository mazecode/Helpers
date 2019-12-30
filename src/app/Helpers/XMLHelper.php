<?php

namespace Siga98\Helpers;

use DOMDocument;
use DOMElement;
use DOMXPath;
use Exception;
use Siga98\XMLLibs\XMLSecurityDSig;
use Siga98\XMLLibs\XMLSecurityKey;

/**
 * Class XMLHelper.
 *
 * https://github.com/robrichards/xmlseclibs/blob/master/src/XMLSecurityDSig.php
 */
final class XMLHelper
{
    public const XMLDSIGNS = 'http://www.w3.org/2000/09/xmldsig#';
    public const SHA1 = 'http://www.w3.org/2000/09/xmldsig#sha1';
    public const SHA256 = 'http://www.w3.org/2001/04/xmlenc#sha256';
    public const SHA384 = 'http://www.w3.org/2001/04/xmldsig-more#sha384';
    public const SHA512 = 'http://www.w3.org/2001/04/xmlenc#sha512';
    public const RIPEMD160 = 'http://www.w3.org/2001/04/xmlenc#ripemd160';
    public const C14N = 'http://www.w3.org/TR/2001/REC-xml-c14n-20010315';
    public const C14N_COMMENTS = 'http://www.w3.org/TR/2001/REC-xml-c14n-20010315#WithComments';
    public const EXC_C14N = 'http://www.w3.org/2001/10/xml-exc-c14n#';
    public const EXC_C14N_COMMENTS = 'http://www.w3.org/2001/10/xml-exc-c14n#WithComments';

    public const TMP = '<ds:Signature xmlns:ds="http://www.w3.org/2000/09/xmldsig#">
  <ds:SignedInfo>
    <ds:SignatureMethod />
  </ds:SignedInfo>
</ds:Signature>';

    public const BASE_TEMPLATE = '<Signature xmlns="http://www.w3.org/2000/09/xmldsig#">
  <SignedInfo>
    <SignatureMethod />
  </SignedInfo>
</Signature>';

    /**
     * @var null|DOMElement
     */
    public $sigNode;

    private $xml;

    /**
     * @var string
     */
    private $prefix = '';

    private $data;

    private $template;

    /**
     * @param string $prefix
     */
    public function __construct($prefix = 'ds')
    {
        $this->data = [];

        $this->template = self::BASE_TEMPLATE;

        if (!empty($prefix)) {
            $this->prefix = "{$prefix}:";
            $search = ['<S', '</S', 'xmlns='];
            $replace = ["<{$prefix}:S", "</{$prefix}:S", "xmlns:{$prefix}="];
            $this->template = \str_replace($search, $replace, $this->template);
        }

        $this->xml = new DOMDocument('1.0', 'UTF-8');
        $this->xml->loadXML($this->template);
        $this->sigNode = $this->xml->documentElement;
    }

    /**
     * @param DOMElement    $parentRef
     * @param string        $cert
     * @param bool          $isPEMFormat
     * @param bool          $isURL
     * @param null|DOMXPath $xpath
     * @param null|array    $options
     *
     * @throws Exception
     */
    public function add509Cert(DOMElement $parentRef, string $cert, bool $isPEMFormat = true, bool $isURL = false, ?DOMXPath $xpath = null, ?array $options = null): void
    {
        if ($isURL) {
            $cert = \file_get_contents($cert);
        }

        if (!$parentRef instanceof DOMElement) {
            throw new Exception('Invalid parent Node parameter');
        }
        $baseDoc = $parentRef->ownerDocument;

        if (null === $xpath) {
            $xpath = new DOMXPath($parentRef->ownerDocument);
            $xpath->registerNamespace('secdsig', self::XMLDSIGNS);
        }
        $query = './secdsig:KeyInfo';
        $nodeset = $xpath->query($query, $parentRef);
        $keyInfo = $nodeset->item(0);
        $dsigPfx = '';

        if (!$keyInfo) {
            $pfx = $parentRef->lookupPrefix(self::XMLDSIGNS);

            if (!empty($pfx)) {
                $dsigPfx = $pfx . ':';
            }
            $inserted = false;
            $keyInfo = $baseDoc->createElementNS(self::XMLDSIGNS, $dsigPfx . 'KeyInfo');
            $query = './secdsig:Object';
            $nodeset = $xpath->query($query, $parentRef);

            if ($sObject = $nodeset->item(0)) {
                $sObject->parentNode->insertBefore($keyInfo, $sObject);
                $inserted = true;
            }

            if (!$inserted) {
                $parentRef->appendChild($keyInfo);
            }
        } else {
            $pfx = $keyInfo->lookupPrefix(self::XMLDSIGNS);

            if (!empty($pfx)) {
                $dsigPfx = $pfx . ':';
            }
        }
        // Add all certs if there are more than one
        $certs = self::staticGet509XCerts($cert, $isPEMFormat);
        // Attach X509 data node
        $x509DataNode = $baseDoc->createElementNS(self::XMLDSIGNS, $dsigPfx . 'X509Data');
        $keyInfo->appendChild($x509DataNode);
        $issuerSerial = false;
        $subjectName = false;

        if (\is_array($options)) {
            if (!empty($options['issuerSerial'])) {
                $issuerSerial = true;
            }

            if (!empty($options['subjectName'])) {
                $subjectName = true;
            }
        }
        // Attach all certificate nodes and any additional data
        foreach ($certs as $X509Cert) {
            if ($issuerSerial || $subjectName) {
                if ($certData = \openssl_x509_parse("-----BEGIN CERTIFICATE-----\n" . \chunk_split($X509Cert, 64, "\n") . "-----END CERTIFICATE-----\n")) {
                    if ($subjectName && !empty($certData['subject'])) {
                        if (\is_array($certData['subject'])) {
                            $parts = [];

                            foreach ($certData['subject'] as $key => $value) {
                                if (\is_array($value)) {
                                    foreach ($value as $valueElement) {
                                        \array_unshift($parts, "{$key}={$valueElement}");
                                    }
                                } else {
                                    \array_unshift($parts, "{$key}={$value}");
                                }
                            }
                            $subjectNameValue = \implode(',', $parts);
                        } else {
                            $subjectNameValue = $certData['issuer'];
                        }
                        $x509SubjectNode = $baseDoc->createElementNS(self::XMLDSIGNS, $dsigPfx . 'X509SubjectName', $subjectNameValue);
                        $x509DataNode->appendChild($x509SubjectNode);
                    }

                    if ($issuerSerial && !empty($certData['issuer']) && !empty($certData['serialNumber'])) {
                        if (\is_array($certData['issuer'])) {
                            $parts = [];

                            foreach ($certData['issuer'] as $key => $value) {
                                \array_unshift($parts, "{$key}={$value}");
                            }
                            $issuerName = \implode(',', $parts);
                        } else {
                            $issuerName = $certData['issuer'];
                        }
                        $x509IssuerNode = $baseDoc->createElementNS(self::XMLDSIGNS, $dsigPfx . 'X509IssuerSerial');
                        $x509DataNode->appendChild($x509IssuerNode);
                        $x509Node = $baseDoc->createElementNS(self::XMLDSIGNS, $dsigPfx . 'X509IssuerName', $issuerName);
                        $x509IssuerNode->appendChild($x509Node);
                        $x509Node = $baseDoc->createElementNS(self::XMLDSIGNS, $dsigPfx . 'X509SerialNumber', $certData['serialNumber']);
                        $x509IssuerNode->appendChild($x509Node);
                    }
                }
            }
            $x509CertNode = $baseDoc->createElementNS(self::XMLDSIGNS, $dsigPfx . 'X509Certificate', $X509Cert);
            $x509DataNode->appendChild($x509CertNode);
        }
    }

    /**
     * @param $xml
     * @param $privateKey
     * @param $passhrase
     * @param $x509Cert
     *
     * @throws \Exception
     *
     * @return string
     */
    public function sign($xml, $privateKey, $passhrase, $x509Cert): ?string
    {
        try {
            return $this->signXML($xml, $privateKey, $passhrase, $x509Cert, null, null, null);
        } catch (Exception $e) {
            throw $e;
        }
    }

    public static function get509XCert($cert, $isPEMFormat = true)
    {
        $certs = self::staticGet509XCerts($cert, $isPEMFormat);

        if (!empty($certs)) {
            return $certs[0];
        }

        return '';
    }

    public static function staticGet509XCerts($certs, $isPEMFormat = true): array
    {
        if ($isPEMFormat) {
            $data = '';
            $certlist = [];
            $arCert = \explode("\n", $certs);
            $inData = false;

            foreach ($arCert as $curData) {
                if (!$inData) {
                    if (0 === \strncmp($curData, '-----BEGIN CERTIFICATE', 22)) {
                        $inData = true;
                    }
                } else {
                    if (0 === \strncmp($curData, '-----END CERTIFICATE', 20)) {
                        $inData = false;
                        $certlist[] = $data;
                        $data = '';

                        continue;
                    }
                    $data .= \trim($curData);
                }
            }

            return $certlist;
        }

        return [$certs];
    }

    /**
     * @param string      $xml
     * @param string      $alg
     * @param             $type
     * @param string      $privateKey
     * @param null|string $passhrase
     * @param string      $x509Cert
     * @param null|string $method
     *
     * @throws \Exception
     *
     * @return string
     */
    public function signXML(string $xml, string $privateKey, ?string $passhrase, string $x509Cert, ?string $method = null, ?string $alg = null, ?string $type = null): string
    {
        $alg = $alg ?? self::SHA256;
        $type = $type ?: XMLSecurityKey::RSA_SHA256;
        $method = $method ?: self::EXC_C14N;

        try {
            // Load the XML to be signed
            $this->xml = new DOMDocument();
            $this->xml->load($xml);

            // Create a new Security object
            $objDSig = new XMLSecurityDSig();
            // Use the c14n exclusive canonicalization
            $objDSig->setCanonicalMethod($method);

            // Sign using SHA-256
            $objDSig->addReference(
                $this->xml,
                $alg,
                ['http://www.w3.org/2000/09/xmldsig#enveloped-signature']
            );

            // Create a new (private) Security key
            $objKey = new XMLSecurityKey($type, ['type' => 'private']);

            // If key has a passphrase, set it using
            if ($passhrase) {
                $objKey->passphrase = $passhrase;
            }

            // Load the private key
            $objKey->loadKey($privateKey, true);

            // Sign the XML file
//            $objDSig->sign($objKey);

            // Add the associated public key to the signature
            $objDSig->add509Cert(\file_get_contents($x509Cert));

            // Append the signature to the XML
            $objDSig->appendSignature($this->xml->documentElement);

            // Save the signed XML
            return $this->xml->saveXML();
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function signXMLs($xml)
    {
        $doc = new \DOMDocument('1.0', 'UTF-8');
        $doc->loadXML($xml);
        $objDSig = new XMLSecurityDSig('');
        $objDSig->setCanonicalMethod(XMLSecurityDSig::EXC_C14N);
        $node = $objDSig->addObject($doc->documentElement);
        $objDSig->addReference(
            $node,
            XMLSecurityDSig::SHA1
        );
        $objKey = new XMLSecurityKey(XMLSecurityKey::RSA_SHA1, ['type' => 'private']);
        $privkey = $this->serverroot . '/certs/dev.key';
        $objKey->loadKey($privkey, true);
        $objDSig->sign($objKey);
        $pubkey = $this->serverroot . '/certs/dev-pub.cer';
        $objDSig->add509Cert(\file_get_contents($pubkey));
        $node->ownerDocument->encoding = 'UTF-8';
        $node->ownerDocument->save(__DIR__ . '/test.xml');

        return $node->ownerDocument->saveXML();
    }
}
