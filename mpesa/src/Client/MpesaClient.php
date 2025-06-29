<?php
/**
 * MpesaClient
 *
 * Handles HTTP communication with the Mpesa API.
 *
 * @package Rndwiga\Mpesa\Client
 * @author Raphael Ndwiga <raphael@raphaelndwiga.africa>
 */

namespace Rndwiga\Mpesa\Client;

use RuntimeException;
use InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Rndwiga\Mpesa\Utils\CacheInterface;
use Rndwiga\Mpesa\Utils\FileCache;
use Rndwiga\Mpesa\Utils\LoggerTrait;

class MpesaClient
{
    use LoggerTrait;
    /**
     * Base URL for Mpesa API in production environment
     */
    protected const MPESA_PRODUCTION_URL = 'https://api.safaricom.co.ke';

    /**
     * Base URL for Mpesa API in sandbox environment
     */
    protected const MPESA_SANDBOX_URL = 'https://sandbox.safaricom.co.ke';

    /**
     * Path to production certificate file
     */
    protected const PRODUCTION_CERT_PATH = __DIR__ . '/../certificates/production_cert.cer';

    /**
     * Path to sandbox certificate file
     */
    protected const SANDBOX_CERT_PATH = __DIR__ . '/../certificates/sandbox_cert.cer';

    /**
     * Consumer key for API authentication
     *
     * @var string
     */
    protected $consumerKey;

    /**
     * Consumer secret for API authentication
     *
     * @var string
     */
    protected $consumerSecret;

    /**
     * Whether to use the live environment
     *
     * @var bool
     */
    protected $isLive;

    /**
     * Access token for API authentication
     *
     * @var string|null
     */
    protected $accessToken;

    /**
     * Cache for storing tokens and other data
     *
     * @var CacheInterface
     */
    protected $cache;

    /**
     * Constructor
     *
     * @param string $consumerKey The consumer key
     * @param string $consumerSecret The consumer secret
     * @param bool $isLive Whether to use the live environment
     * @param LoggerInterface|null $logger The logger instance
     * @param CacheInterface|null $cache The cache instance
     */
    public function __construct(
        string $consumerKey, 
        string $consumerSecret, 
        bool $isLive = false, 
        ?LoggerInterface $logger = null,
        ?CacheInterface $cache = null
    ) {
        $this->consumerKey = $consumerKey;
        $this->consumerSecret = $consumerSecret;
        $this->isLive = $isLive;
        $this->setLogger($logger);
        $this->cache = $cache ?? new FileCache();

        $this->logInfo('MpesaClient initialized', [
            'environment' => $isLive ? 'production' : 'sandbox',
            'baseUrl' => $this->getBaseUrl()
        ]);
    }

    /**
     * Get the base URL for Mpesa API based on environment
     *
     * @return string The base URL
     */
    public function getBaseUrl(): string
    {
        return $this->isLive ? self::MPESA_PRODUCTION_URL : self::MPESA_SANDBOX_URL;
    }

    /**
     * Generate an access token for Mpesa API authentication
     *
     * @return string The access token
     * @throws RuntimeException If the consumer key or secret is not provided
     */
    public function getAccessToken(): string
    {
        // Check if we have a token in memory
        if ($this->accessToken) {
            $this->logDebug('Using in-memory access token');
            return $this->accessToken;
        }

        // Check if we have a token in cache
        $cacheKey = 'mpesa_access_token_' . md5($this->consumerKey . $this->isLive);
        $cachedToken = $this->cache->get($cacheKey);

        if ($cachedToken) {
            $this->logDebug('Using cached access token');
            $this->accessToken = $cachedToken;
            return $this->accessToken;
        }

        $this->logInfo('Requesting new access token');

        if (empty($this->consumerKey) || empty($this->consumerSecret)) {
            $this->logError('Missing consumer key or secret');
            throw new RuntimeException("Consumer key and consumer secret are required");
        }

        $url = $this->getBaseUrl() . '/oauth/v1/generate?grant_type=client_credentials';
        $this->logDebug('Access token URL', ['url' => $url]);

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        $credentials = base64_encode("{$this->consumerKey}:{$this->consumerSecret}");
        curl_setopt($curl, CURLOPT_HTTPHEADER, ['Authorization: Basic ' . $credentials]);
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, $this->isLive); // Only verify SSL in production
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        $this->logDebug('Making access token request');
        $response = curl_exec($curl);

        if ($response === false) {
            $error = curl_error($curl);
            curl_close($curl);
            $this->logError('Failed to get access token', ['error' => $error]);
            throw new RuntimeException("cURL Error: " . $error);
        }

        curl_close($curl);

        $decoded = json_decode($response);

        if (!isset($decoded->access_token)) {
            $this->logError('Invalid access token response', ['response' => $response]);
            throw new RuntimeException("Failed to get access token: " . $response);
        }

        $this->accessToken = $decoded->access_token;

        // Cache the token - typically valid for 1 hour (3600 seconds)
        // We'll cache it for slightly less to ensure we refresh before it expires
        $ttl = isset($decoded->expires_in) ? (int)$decoded->expires_in - 60 : 3540;
        $this->cache->set($cacheKey, $this->accessToken, $ttl);

        $this->logInfo('Access token obtained and cached successfully', ['ttl' => $ttl]);
        return $this->accessToken;
    }

    /**
     * Generate security credentials for Mpesa API authentication
     *
     * @param string $initiatorPassword The initiator password to encrypt
     * @return string The encrypted security credential
     * @throws RuntimeException If encryption fails
     */
    public function generateSecurityCredentials(string $initiatorPassword): string
    {
        $this->logInfo('Generating security credentials');

        if (empty($initiatorPassword)) {
            $this->logError('Empty initiator password provided');
            throw new InvalidArgumentException("Initiator password is required");
        }

        try {
            // Get the appropriate certificate based on environment
            $certPath = $this->isLive ? self::PRODUCTION_CERT_PATH : self::SANDBOX_CERT_PATH;
            $this->logDebug('Using certificate path', ['path' => $certPath, 'environment' => $this->isLive ? 'production' : 'sandbox']);

            // If a certificate file exists, use it
            if (file_exists($certPath)) {
                $this->logDebug('Using certificate file from path');
                $publicKey = file_get_contents($certPath);
            } else {
                // Otherwise use the hardcoded certificate (fallback)
                $this->logWarning('Certificate file not found, using hardcoded certificate');
                $publicKey = $this->getCertificateContent();
            }

            // Encrypt the password
            $this->logDebug('Encrypting initiator password');
            $encrypted = '';
            $result = openssl_public_encrypt($initiatorPassword, $encrypted, $publicKey, OPENSSL_PKCS1_PADDING);

            if (!$result) {
                $error = openssl_error_string();
                $this->logError('Failed to encrypt initiator password', ['error' => $error]);
                throw new RuntimeException("Failed to encrypt initiator password: " . $error);
            }

            $this->logInfo('Security credentials generated successfully');
            return base64_encode($encrypted);
        } catch (\Exception $e) {
            $this->logError('Security credential generation failed', ['error' => $e->getMessage()]);
            throw new RuntimeException("Security credential generation failed: " . $e->getMessage());
        }
    }

    /**
     * Get the certificate content based on environment
     *
     * @return string The certificate content
     */
    protected function getCertificateContent(): string
    {
        if ($this->isLive) {
            return "-----BEGIN CERTIFICATE-----
MIIGkzCCBXugAwIBAgIKXfBp5gAAAD+hNjANBgkqhkiG9w0BAQsFADBbMRMwEQYK
CZImiZPyLGQBGRYDbmV0MRkwFwYKCZImiZPyLGQBGRYJc2FmYXJpY29tMSkwJwYD
VQQDEyBTYWZhcmljb20gSW50ZXJuYWwgSXNzdWluZyBDQSAwMjAeFw0xNzA0MjUx
NjA3MjRaFw0xODAzMjExMzIwMTNaMIGNMQswCQYDVQQGEwJLRTEQMA4GA1UECBMH
TmFpcm9iaTEQMA4GA1UEBxMHTmFpcm9iaTEaMBgGA1UEChMRU2FmYXJpY29tIExp
bWl0ZWQxEzARBgNVBAsTClRlY2hub2xvZ3kxKTAnBgNVBAMTIGFwaWdlZS5hcGlj
YWxsZXIuc2FmYXJpY29tLmNvLmtlMIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIB
CgKCAQEAoknIb5Tm1hxOVdFsOejAs6veAai32Zv442BLuOGkFKUeCUM2s0K8XEsU
t6BP25rQGNlTCTEqfdtRrym6bt5k0fTDscf0yMCoYzaxTh1mejg8rPO6bD8MJB0c
FWRUeLEyWjMeEPsYVSJFv7T58IdAn7/RhkrpBl1dT7SmIZfNVkIlD35+Cxgab+u7
+c7dHh6mWguEEoE3NbV7Xjl60zbD/Buvmu6i9EYz+27jNVPI6pRXHvp+ajIzTSsi
eD8Ztz1eoC9mphErasAGpMbR1sba9bM6hjw4tyTWnJDz7RdQQmnsW1NfFdYdK0qD
RKUX7SG6rQkBqVhndFve4SDFRq6wvQIDAQABo4IDJDCCAyAwHQYDVR0OBBYEFG2w
ycrgEBPFzPUZVjh8KoJ3EpuyMB8GA1UdIwQYMBaAFOsy1E9+YJo6mCBjug1evuh5
TtUkMIIBOwYDVR0fBIIBMjCCAS4wggEqoIIBJqCCASKGgdZsZGFwOi8vL0NOPVNh
ZmFyaWNvbSUyMEludGVybmFsJTIwSXNzdWluZyUyMENBJTIwMDIsQ049U1ZEVDNJ
U1NDQTAxLENOPUNEUCxDTj1QdWJsaWMlMjBLZXklMjBTZXJ2aWNlcyxDTj1TZXJ2
aWNlcyxDTj1Db25maWd1cmF0aW9uLERDPXNhZmFyaWNvbSxEQz1uZXQ/Y2VydGlm
aWNhdGVSZXZvY2F0aW9uTGlzdD9iYXNlP29iamVjdENsYXNzPWNSTERpc3RyaWJ1
dGlvblBvaW50hkdodHRwOi8vY3JsLnNhZmFyaWNvbS5jby5rZS9TYWZhcmljb20l
MjBJbnRlcm5hbCUyMElzc3VpbmclMjBDQSUyMDAyLmNybDCCAQkGCCsGAQUFBwEB
BIH8MIH5MIHJBggrBgEFBQcwAoaBvGxkYXA6Ly8vQ049U2FmYXJpY29tJTIwSW50
ZXJuYWwlMjBJc3N1aW5nJTIwQ0ElMjAwMixDTj1BSUEsQ049UHVibGljJTIwS2V5
JTIwU2VydmljZXMsQ049U2VydmljZXMsQ049Q29uZmlndXJhdGlvbixEQz1zYWZh
cmljb20sREM9bmV0P2NBQ2VydGlmaWNhdGU/YmFzZT9vYmplY3RDbGFzcz1jZXJ0
aWZpY2F0aW9uQXV0aG9yaXR5MCsGCCsGAQUFBzABhh9odHRwOi8vY3JsLnNhZmFy
aWNvbS5jby5rZS9vY3NwMAsGA1UdDwQEAwIFoDA9BgkrBgEEAYI3FQcEMDAuBiYr
BgEEAYI3FQiHz4xWhMLEA4XphTaE3tENhqCICGeGwcdsg7m5awIBZAIBDDAdBgNV
HSUEFjAUBggrBgEFBQcDAgYIKwYBBQUHAwEwJwYJKwYBBAGCNxUKBBowGDAKBggr
BgEFBQcDAjAKBggrBgEFBQcDATANBgkqhkiG9w0BAQsFAAOCAQEAC/hWx7KTwSYr
x2SOyyHNLTRmCnCJmqxA/Q+IzpW1mGtw4Sb/8jdsoWrDiYLxoKGkgkvmQmB2J3zU
ngzJIM2EeU921vbjLqX9sLWStZbNC2Udk5HEecdpe1AN/ltIoE09ntglUNINyCmf
zChs2maF0Rd/y5hGnMM9bX9ub0sqrkzL3ihfmv4vkXNxYR8k246ZZ8tjQEVsKehE
dqAmj8WYkYdWIHQlkKFP9ba0RJv7aBKb8/KP+qZ5hJip0I5Ey6JJ3wlEWRWUYUKh
gYoPHrJ92ToadnFCCpOlLKWc0xVxANofy6fqreOVboPO0qTAYpoXakmgeRNLUiar
0ah6M/q/KA==
-----END CERTIFICATE-----";
        } else {
            return "-----BEGIN CERTIFICATE-----
MIIGgDCCBWigAwIBAgIKMvrulAAAAARG5DANBgkqhkiG9w0BAQsFADBbMRMwEQYK
CZImiZPyLGQBGRYDbmV0MRkwFwYKCZImiZPyLGQBGRYJc2FmYXJpY29tMSkwJwYD
VQQDEyBTYWZhcmljb20gSW50ZXJuYWwgSXNzdWluZyBDQSAwMjAeFw0xNDExMTIw
NzEyNDVaFw0xNjExMTEwNzEyNDVaMHsxCzAJBgNVBAYTAktFMRAwDgYDVQQIEwdO
YWlyb2JpMRAwDgYDVQQHEwdOYWlyb2JpMRAwDgYDVQQKEwdOYWlyb2JpMRMwEQYD
VQQLEwpUZWNobm9sb2d5MSEwHwYDVQQDExhhcGljcnlwdC5zYWZhcmljb20uY28u
a2UwggEiMA0GCSqGSIb3DQEBAQUAA4IBDwAwggEKAoIBAQCotwV1VxXsd0Q6i2w0
ugw+EPvgJfV6PNyB826Ik3L2lPJLFuzNEEJbGaiTdSe6Xitf/PJUP/q8Nv2dupHL
BkiBHjpQ6f61He8Zdc9fqKDGBLoNhNpBXxbznzI4Yu6hjBGLnF5Al9zMAxTij6wL
GUFswKpizifNbzV+LyIXY4RR2t8lxtqaFKeSx2B8P+eiZbL0wRIDPVC5+s4GdpFf
Y3QIqyLxI2bOyCGl8/XlUuIhVXxhc8Uq132xjfsWljbw4oaMobnB2KN79vMUvyoR
w8OGpga5VoaSFfVuQjSIf5RwW1hitm/8XJvmNEdeY0uKriYwbR8wfwQ3E0AIW1Fl
MMghAgMBAAGjggMkMIIDIDAdBgNVHQ4EFgQUwUfE+NgGndWDN3DyVp+CAiF1Zkgw
HwYDVR0jBBgwFoAU6zLUT35gmjqYIGO6DV6+6HlO1SQwggE7BgNVHR8EggEyMIIB
LjCCASqgggEmoIIBIoaB1mxkYXA6Ly8vQ049U2FmYXJpY29tJTIwSW50ZXJuYWwl
MjBJc3N1aW5nJTIwQ0ElMjAwMixDTj1TVkRUM0lTU0NBMDEsQ049Q0RQLENOPVB1
YmxpYyUyMEtleSUyMFNlcnZpY2VzLENOPVNlcnZpY2VzLENOPUNvbmZpZ3VyYXRp
b24sREM9c2FmYXJpY29tLERDPW5ldD9jZXJ0aWZpY2F0ZVJldm9jYXRpb25MaXN0
P2Jhc2U/b2JqZWN0Q2xhc3M9Y1JMRGlzdHJpYnV0aW9uUG9pbnSGR2h0dHA6Ly9j
cmwuc2FmYXJpY29tLmNvLmtlL1NhZmFyaWNvbSUyMEludGVybmFsJTIwSXNzdWlu
ZyUyMENBJTIwMDIuY3JsMIIBCQYIKwYBBQUHAQEEgfwwgfkwgckGCCsGAQUFBzAC
hoG8bGRhcDovLy9DTj1TYWZhcmljb20lMjBJbnRlcm5hbCUyMElzc3VpbmclMjBD
QSUyMDAyLENOPUFJQSxDTj1QdWJsaWMlMjBLZXklMjBTZXJ2aWNlcyxDTj1TZXJ2
aWNlcyxDTj1Db25maWd1cmF0aW9uLERDPXNhZmFyaWNvbSxEQz1uZXQ/Y0FDZXJ0
aWZpY2F0ZT9iYXNlP29iamVjdENsYXNzPWNlcnRpZmljYXRpb25BdXRob3JpdHkw
KwYIKwYBBQUHMAGGH2h0dHA6Ly9jcmwuc2FmYXJpY29tLmNvLmtlL29jc3AwCwYD
VR0PBAQDAgWgMD0GCSsGAQQBgjcVBwQwMC4GJisGAQQBgjcVCIfPjFaEwsQDhemF
NoTe0Q2GoIgIZ4bBx2yDublrAgFkAgEMMB0GA1UdJQQWMBQGCCsGAQUFBwMCBggr
BgEFBQcDATAnBgkrBgEEAYI3FQoEGjAYMAoGCCsGAQUFBwMCMAoGCCsGAQUFBwMB
MA0GCSqGSIb3DQEBCwUAA4IBAQBMFKlncYDI06ziR0Z0/reptIJRCMo+rqo/cUuP
KMmJCY3sXxFHs5ilNXo8YavgRLpxJxdZMkiUIVuVaBanXkz9/nMriiJJwwcMPjUV
9nQqwNUEqrSx29L1ARFdUy7LhN4NV7mEMde3MQybCQgBjjOPcVSVZXnaZIggDYIU
w4THLy9rDmUIasC8GDdRcVM8xDOVQD/Pt5qlx/LSbTNe2fekhTLFIGYXJVz2rcsj
k1BfG7P3pXnsPAzu199UZnqhEF+y/0/nNpf3ftHZjfX6Ws+dQuLoDN6pIl8qmok9
9E/EAgL1zOIzFvCRYlnjKdnsuqL1sIYFBlv3oxo6W1O+X9IZ
-----END CERTIFICATE-----";
        }
    }

    /**
     * Make a POST request to the Mpesa API
     *
     * @param string $endpoint The API endpoint to call
     * @param array $data The data to send in the request
     * @param bool $verifySSL Whether to verify SSL certificates
     * @return string The response from the API
     * @throws RuntimeException If the request fails
     */
    public function post(string $endpoint, array $data, bool $verifySSL = true): string
    {
        $url = $this->getBaseUrl() . $endpoint;
        $this->logInfo('Making API request', [
            'endpoint' => $endpoint,
            'url' => $url,
            'verifySSL' => $verifySSL
        ]);

        // Mask sensitive data for logging
        $logData = $this->maskSensitiveData($data);
        $this->logDebug('Request data', ['data' => $logData]);

        $token = $this->getAccessToken();

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $token
        ]);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, $verifySSL);

        $this->logDebug('Executing API request');
        $response = curl_exec($curl);
        $error = curl_error($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        curl_close($curl);

        if ($response === false) {
            $this->logError('API request failed', [
                'error' => $error,
                'endpoint' => $endpoint
            ]);
            throw new RuntimeException("cURL Error: " . $error);
        }

        $this->logInfo('API request completed', [
            'endpoint' => $endpoint,
            'httpCode' => $httpCode
        ]);
        $this->logDebug('API response', ['response' => $response]);

        return $response;
    }

    /**
     * Mask sensitive data for logging
     *
     * @param array $data The data to mask
     * @return array The masked data
     */
    protected function maskSensitiveData(array $data): array
    {
        $maskedData = $data;

        // List of sensitive fields to mask
        $sensitiveFields = [
            'SecurityCredential',
            'Password',
            'InitiatorPassword',
            'ConsumerKey',
            'ConsumerSecret'
        ];

        foreach ($sensitiveFields as $field) {
            if (isset($maskedData[$field])) {
                $maskedData[$field] = '******';
            }
        }

        return $maskedData;
    }

    /**
     * Send a response to Mpesa API to confirm transaction processing
     *
     * @param string $message Optional custom message
     * @return string JSON response
     */
    public function finishTransaction(string $message = "Confirmation Service request accepted successfully"): string
    {
        $resultArray = [
            "ResultDesc" => $message,
            "ResultCode" => "0"
        ];

        return json_encode($resultArray);
    }
}
