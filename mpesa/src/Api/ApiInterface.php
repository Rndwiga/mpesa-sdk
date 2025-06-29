<?php
/**
 * ApiInterface
 *
 * Interface for all Mpesa API handlers.
 *
 * @package Rndwiga\Mpesa\Api
 * @author Raphael Ndwiga <raphael@raphaelndwiga.africa>
 */

namespace Rndwiga\Mpesa\Api;

interface ApiInterface
{
    /**
     * Send a request to the Mpesa API
     *
     * @param array $params The parameters to send in the request
     * @param bool $verifySSL Whether to verify SSL certificates
     * @return mixed The response from the API
     */
    public function sendRequest(array $params, bool $verifySSL = true);
}