<?php

namespace Waffle\Helper;

use Waffle\Application as Waffle;
use Waffle\Exception\UpdateCheckException;

class GitHubHelper
{

    /**
     * Gets the latest release data from the GitHub api for the
     * specified repository.
     *
     * @throws UpdateCheckException
     *
     * @param string
     *   The repository to check (ie waffle-ops/waffle).
     *
     * @return array
     */
    public function getLatestRelease(string $repository)
    {
        // TODO: Throw exceptions.
        $url = sprintf('https://api.github.com/repos/%s/releases/latest', $repository);

        // GitHub requires a user agent header when connecting to the api.
        $userAgent = sprintf('User-Agent: %s (%s) (PHP)', Waffle::NAME, Waffle::REPOSITORY);

        // This should be a fast call. This is called everytime Waffle starts
        // up at the moment so if the network is slow, I'd rather throw an
        // exception than wait the default timeout in the ini settings.
        $timeout = 2;

        $opts = [
            'http' => [
                'method' => 'GET',
                'header' => [ $userAgent ],
                'timeout' => $timeout,
            ],
        ];

        $context = stream_context_create($opts);

        // Suppressing warnings, but have a check below to throw exception.
        $response_json = @file_get_contents($url, false, $context);

        if ($response_json === false) {
            $error = error_get_last();
            throw new UpdateCheckException(
                sprintf(
                    'Unable to get release information for %s. \n Error: %s',
                    $repository,
                    $error['message']
                )
            );
        }

        $response = json_decode($response_json, true);

        return $response;
    }
}
