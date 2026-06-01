<?php

namespace Cloudbadak\PaymentHub\Driver;

use Cloudbadak\PaymentHub\Exceptions\RequestClientException;
use Cloudbadak\PaymentHub\Exceptions\RequestServerException;

class ApiRequest
{
    protected string $baseUrl;
    protected int $timeOut;

    private int $responseCode = 400;
    private array $headers = [];

    public function __construct(string $baseUrl, int $timeOut = 5, array $headers = [])
    {
        $this->baseUrl = $baseUrl;
        $this->timeOut = $timeOut;
        $this->headers = $headers;
    }

    public function getResponseCode(): int
    {
        return $this->responseCode;
    }

    public function post(string $endpoint, array $data = [], array $headers = [])
    {
        $headers = array_merge($this->headers, $headers);
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => trim($this->baseUrl, '/') . '/' . ltrim($endpoint, '/'),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_MAXREDIRS => 5,
            CURLOPT_TIMEOUT => $this->timeOut,
            CURLOPT_CONNECTTIMEOUT => 3,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => array_merge(array(
                'Content-Type: application/json',
            ), $headers),
        ));

        $response = curl_exec($curl);
        $this->responseCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        if (curl_errno($curl)) {
            $error = curl_error($curl);
            curl_close($curl);
            throw new RequestClientException('API request error: ' . $error);
        }

        curl_close($curl);
        if (json_decode($response) === null) {
            throw new RequestServerException('API response is not valid JSON: ' . $response);
        }

        return json_decode($response, true);
    }

    public function get(string $endpoint, array $params = [], array $headers = [])
    {
        $headers = array_merge($this->headers, $headers);
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => trim($this->baseUrl, '/') . '/' . ltrim($endpoint, '/') . '?' . http_build_query($params),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_MAXREDIRS => 5,
            CURLOPT_TIMEOUT => $this->timeOut,
            CURLOPT_CONNECTTIMEOUT => 3,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => array_merge(array(
                'Content-Type: application/json',
            ), $headers),
        ));

        $response = curl_exec($curl);
        $this->responseCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        if (curl_errno($curl)) {
            $error = curl_error($curl);
            curl_close($curl);
            throw new RequestClientException('API request error: ' . $error);
        }

        curl_close($curl);
        if (json_decode($response) === null) {
            throw new RequestServerException('API response is not valid JSON: ' . $response);
        }

        return json_decode($response, true);
    }
}