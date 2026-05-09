<?php

declare(strict_types=1);

namespace Plugins\FiscalServices\Services\Concerns\CostaRica;

use DOMDocument;
use RobRichards\XMLSecLibs\XMLSecurityKey;
use RobRichards\XMLSecLibs\XMLSecurityDSig;

/**
 * XML Signer for Costa Rica Electronic Invoicing.
 *
 * Signs XML documents using the certificate provided by the Costa Rica
 * Ministry of Finance for electronic invoice submission.
 */
class XmlSigner
{
    public function __construct(
        private string $certificatePath,
        private string $certificatePin,
    ) {}

    /**
     * Sign an XML document.
     *
     * @param  string  $xmlContent  The XML content to sign
     * @return string|false The signed XML or false on failure
     */
    public function sign(string $xmlContent): string|false
    {
        try {
            $xml = new DOMDocument('1.0', 'UTF-8');
            $xml->loadXML($xmlContent);

            // Load certificate
            $certInfo = $this->loadCertificate();

            if (! $certInfo) {
                return false;
            }

            // Create signature object
            $objDSig = new XMLSecurityDSig;
            $objDSig->setCanonicalMethod(XMLSecurityDSig::C14N);

            // Add reference to the document
            $objDSig->addReference(
                $xml,
                XMLSecurityDSig::SHA256,
                ['http://www.w3.org/2000/09/xmldsig#enveloped-signature'],
                ['force_uri' => true],
            );

            // Create private key object
            $objKey = new XMLSecurityKey(XMLSecurityKey::RSA_SHA256, ['type' => 'private']);
            $objKey->loadKey($certInfo['privateKey']);

            // Sign the XML
            $objDSig->sign($objKey);

            // Add the certificate to the signature
            $objDSig->add509Cert($certInfo['publicKey'], true);

            // Append signature to document
            $objDSig->appendSignature($xml->documentElement);

            return $xml->saveXML();
        } catch (\Throwable $e) {
            logger()->error('Costa Rica XML signing failed.', [
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Load certificate information from PEM or P12 file.
     */
    private function loadCertificate(): ?array
    {
        $certPath = $this->resolveCertificatePath();

        if (! file_exists($certPath)) {
            logger()->error('Costa Rica certificate file not found.', [
                'path' => $certPath,
            ]);

            return null;
        }

        $extension = strtolower(pathinfo($certPath, PATHINFO_EXTENSION));

        return match ($extension) {
            'pem' => $this->loadPemCertificate($certPath),
            'p12', 'pfx' => $this->loadP12Certificate($certPath),
            default => $this->loadPemCertificate($certPath),
        };
    }

    /**
     * Load a PEM format certificate.
     */
    private function loadPemCertificate(string $certPath): ?array
    {
        $pemContent = file_get_contents($certPath);

        if ($pemContent === false || trim($pemContent) === '') {
            return null;
        }

        // Extract certificate
        if (! preg_match('/-----BEGIN CERTIFICATE-----(.*?)-----END CERTIFICATE-----/s', $pemContent, $certMatch)) {
            logger()->error('No certificate found in PEM file.');

            return null;
        }

        // Extract private key
        if (! preg_match('/-----BEGIN (?:RSA )?PRIVATE KEY-----(.*?)-----END (?:RSA )?PRIVATE KEY-----/s', $pemContent, $keyMatch)) {
            logger()->error('No private key found in PEM file.');

            return null;
        }

        return [
            'publicKey'  => $certMatch[0],
            'privateKey' => $keyMatch[0],
        ];
    }

    /**
     * Load a P12/PFX format certificate.
     */
    private function loadP12Certificate(string $certPath): ?array
    {
        $p12Content = file_get_contents($certPath);

        if ($p12Content === false) {
            return null;
        }

        $certs = [];

        if (! openssl_pkcs12_read($p12Content, $certs, $this->certificatePin)) {
            logger()->error('Failed to read P12 certificate. Check password.');

            return null;
        }

        return [
            'publicKey'  => $certs['cert'] ?? null,
            'privateKey' => $certs['pkey'] ?? null,
        ];
    }

    /**
     * Resolve the certificate path.
     */
    private function resolveCertificatePath(): string
    {
        if (file_exists($this->certificatePath)) {
            return $this->certificatePath;
        }

        // Try relative to storage
        $storagePath = storage_path('app/certificates/' . $this->certificatePath);

        if (file_exists($storagePath)) {
            return $storagePath;
        }

        // Try base path
        $basePath = base_path($this->certificatePath);

        if (file_exists($basePath)) {
            return $basePath;
        }

        return $this->certificatePath;
    }
}
