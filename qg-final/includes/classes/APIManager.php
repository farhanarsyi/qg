<?php
/**
 * API Manager Class
 * Handles all API-related operations
 */

class APIManager {
    
    private $logger;
    private $accessToken;
    private $tokenExpiry;
    
    public function __construct() {
        $this->logger = new Logger('API');
    }
    
    /**
     * Get access token using client credentials
     * @return string|null Access token
     */
    public function getAccessToken() {
        // Check if we have a valid cached token
        if ($this->accessToken && $this->tokenExpiry > time()) {
            return $this->accessToken;
        }
        
        try {
            $ch = curl_init(API_TOKEN_URL);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/x-www-form-urlencoded'
            ]);
            curl_setopt($ch, CURLOPT_POSTFIELDS, "grant_type=client_credentials");
            curl_setopt($ch, CURLOPT_USERPWD, SSO_CLIENT_ID . ":" . SSO_CLIENT_SECRET);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_TIMEOUT, QG_API_TIMEOUT);
            
            $response = curl_exec($ch);
            
            if (curl_errno($ch)) {
                throw new Exception('cURL Error: ' . curl_error($ch));
            }
            
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($httpCode !== 200) {
                throw new Exception('HTTP Error: ' . $httpCode);
            }
            
            $data = json_decode($response, true);
            
            if (isset($data['access_token'])) {
                $this->accessToken = $data['access_token'];
                $this->tokenExpiry = time() + ($data['expires_in'] ?? 3600) - 60; // Subtract 60s for safety
                
                $this->logger->info('Access token obtained successfully');
                return $this->accessToken;
            }
            
            throw new Exception('Invalid token response');
            
        } catch (Exception $e) {
            $this->logger->error('Failed to get access token: ' . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Get employee data by username
     * @param string $username Employee username
     * @return array|null Employee data
     */
    public function getPegawaiByUsername($username) {
        $accessToken = $this->getAccessToken();
        if (!$accessToken) {
            return null;
        }
        
        try {
            $url = API_PEGAWAI_URL . '/username/' . urlencode($username);
            
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $accessToken
            ]);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_TIMEOUT, QG_API_TIMEOUT);
            
            $response = curl_exec($ch);
            
            if (curl_errno($ch)) {
                throw new Exception('cURL Error: ' . curl_error($ch));
            }
            
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($httpCode === 200) {
                $data = json_decode($response, true);
                $this->logger->info('Employee data retrieved', ['username' => $username]);
                return $data;
            } elseif ($httpCode === 404) {
                $this->logger->warning('Employee not found', ['username' => $username]);
                return null;
            } else {
                throw new Exception('HTTP Error: ' . $httpCode);
            }
            
        } catch (Exception $e) {
            $this->logger->error('Failed to get employee data: ' . $e->getMessage(), ['username' => $username]);
            return null;
        }
    }
    
    /**
     * Make a general API request
     * @param string $method HTTP method (GET, POST, etc.)
     * @param string $url API endpoint URL
     * @param array $data Request data
     * @param array $headers Additional headers
     * @return array|null Response data
     */
    public function makeRequest($method, $url, $data = null, $headers = []) {
        try {
            $ch = curl_init($url);
            
            // Default headers
            $defaultHeaders = ['Content-Type: application/json'];
            $headers = array_merge($defaultHeaders, $headers);
            
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_TIMEOUT, QG_API_TIMEOUT);
            
            // Set method and data
            switch (strtoupper($method)) {
                case 'POST':
                    curl_setopt($ch, CURLOPT_POST, true);
                    if ($data) {
                        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
                    }
                    break;
                case 'PUT':
                    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
                    if ($data) {
                        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
                    }
                    break;
                case 'DELETE':
                    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
                    break;
                case 'GET':
                default:
                    // GET is default
                    break;
            }
            
            $response = curl_exec($ch);
            
            if (curl_errno($ch)) {
                throw new Exception('cURL Error: ' . curl_error($ch));
            }
            
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            $responseData = json_decode($response, true);
            
            $this->logger->info('API request completed', [
                'method' => $method,
                'url' => $url,
                'status' => $httpCode
            ]);
            
            return [
                'status' => $httpCode,
                'data' => $responseData,
                'success' => $httpCode >= 200 && $httpCode < 300
            ];
            
        } catch (Exception $e) {
            $this->logger->error('API request failed: ' . $e->getMessage(), [
                'method' => $method,
                'url' => $url
            ]);
            
            return [
                'status' => 0,
                'data' => null,
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Get regional data for Quality Gates filtering
     * @param array $filter Wilayah filter configuration
     * @return array Regional data
     */
    public function getRegionalData($filter) {
        // This would typically call your internal API
        // For now, return dummy data structure
        return [
            'provinces' => [],
            'districts' => [],
            'filter_applied' => $filter
        ];
    }
} 