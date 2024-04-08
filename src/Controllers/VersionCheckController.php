<?php

declare(strict_types=1);

namespace PhpMyAdmin\Controllers;

use PhpMyAdmin\Core;
use PhpMyAdmin\Http\Response;
use PhpMyAdmin\Http\ServerRequest;
use PhpMyAdmin\ResponseRenderer;
use PhpMyAdmin\VersionInformation;

use function header;
use function json_encode;
use function sprintf;

/**
 * A caching proxy for retrieving version information from https://www.phpmyadmin.net/.
 */
final class VersionCheckController implements InvocableController
{
    public function __construct(
        private readonly ResponseRenderer $response,
        private readonly VersionInformation $versionInformation,
    ) {
    }

    public function __invoke(ServerRequest $request): Response|null
    {
        $_GET['ajax_request'] = 'true';

        // Disabling standard response.
        $this->response->disable();

        // Always send the correct headers
        foreach (Core::headerJSON() as $name => $value) {
            header(sprintf('%s: %s', $name, $value));
        }

        $versionDetails = $this->versionInformation->getLatestVersions();

        if ($versionDetails === null) {
            echo json_encode([]);

            return null;
        }

        $latestCompatible = $this->versionInformation->getLatestCompatibleVersion($versionDetails);
        $version = '';
        $date = '';
        if ($latestCompatible != null) {
            $version = $latestCompatible->version;
            $date = $latestCompatible->date;
        }

        echo json_encode(['version' => $version, 'date' => $date]);

        return null;
    }
}
