<?php

namespace app\infrastructure\requests;

use Exception;
use Yii;

/**
 * Generic request via curl extension.
 * Without any additional configuration execution of this class
 * with make a GET request to specified url and return raw response body.
 */
class CurlRequest
{
    /** @var array Request data */
    protected array $data = [];
    /** @var string Request method that will be used */
    private string $requestMethod = 'GET';
    /** @var array Headers for the request */
    private array $headers = [];
    /** @var array Curl config values for the request */
    private array $curl_options = [];

    /**
     * @param string $url Base request url
     */
    public function __construct(protected readonly string $url)
    {
        $this->init();
    }

    /**
     * Method will be called from the constructor.
     * Can be used to perform additional initialization logic
     * without the need to override the constructor.
     *
     * @return void
     */
    protected function init(): void
    {

    }

    /**
     * Sets request data.
     * This will override any previously set data.
     *
     * @param array $data Data for request
     * @return void
     */
    public function setData(array $data): void
    {
        $this->data = $data;
    }

    /**
     * Sets request to use POST method.
     *
     * @return void
     */
    public function usePost(): void
    {
        $this->requestMethod = 'POST';
    }

    /**
     * Sets request to use GET method (default).
     *
     * @return void
     */
    public function useGet(): void
    {
        $this->requestMethod = 'GET';
    }

    /**
     * Returns request method that will be used.
     *
     * @return string
     */
    public function getRequestMethod(): string
    {
        return $this->requestMethod;
    }

    /**
     * Sets header for the request.
     *
     * @param string $header Header name
     * @param string $value Header value
     * @return void
     */
    public function setHeader(string $header, string $value): void
    {
        $this->headers[$header] = $value;
    }

    /**
     * Executes the request and returns the result.
     *
     * @return mixed
     * @throws Exception
     */
    public function execute(): mixed
    {
        $this->buildCurlConfig();

        $ch = curl_init($this->buildUrl());
        foreach ($this->curl_options as $option => $value) {
            curl_setopt($ch, $option, $value);
        }
        $result = curl_exec($ch);

        $responseCode = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        if ($responseCode !== 200) {
            $this->handleRequestError($result, $responseCode);
        }

        return $this->processResponseData($result);
    }

    /**
     * @return void
     */
    protected function buildCurlConfig(): void
    {
        $this->curl_options = [
            CURLOPT_RETURNTRANSFER => true,
        ];

        if ($this->requestMethod == 'POST') {
            $this->curl_options[CURLOPT_POSTFIELDS] = json_encode($this->data);
            $this->curl_options[CURLOPT_POST] = true;
        }

        if (!empty($this->headers)) {
            $headers = array_map(function ($header, $value) {
                return $header . ': ' . $value;
            }, array_keys($this->headers), $this->headers);
            $this->curl_options[CURLOPT_HTTPHEADER] = $headers;
        }
    }

    /**
     * Returns the request URL.
     *
     * @return string
     */
    protected function buildUrl(): string
    {
        $url = $this->url;
        if ($this->requestMethod === 'GET' && !empty($this->data)) {
            $url .= '?' . http_build_query($this->data);
        }

        return $url;
    }

    /**
     * Handles error response code on response.
     * This method can be used to perform additional operations like logging before throwing an exception.
     * It can also be used to override the result and allow the execution to complete as successful with
     * custom return data in case of error.
     *
     * @param bool|string $result Reference to raw response body
     * @param mixed $responseCode Response code for the request
     * @return mixed
     * @throws Exception
     */
    protected function handleRequestError(bool|string &$result, mixed $responseCode): mixed
    {
        Yii::debug($result);
        throw new Exception('Request failed with code ' . $responseCode);
    }

    /**
     * Performs additional processing of the response data.
     * Can be used to format or modify the data.
     *
     * @param bool|string $result Raw response body
     * @return mixed
     */
    protected function processResponseData(bool|string $result): mixed
    {
        return $result;
    }
}
