<?php

namespace App\Service;

use App\Dto\GitlabResponseDto;
use App\Entity\Project;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Psr\Cache\InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;

class GitlabRequestService
{
    /**
     * @var Client
     */
    private $client;

    /**
     * @var FilesystemAdapter
     */
    private $cache;

    /**
     * @var int
     */
    private $cache_ttl;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(Client $client, LoggerInterface $logger, int $cache_ttl = 3600)
    {
        $this->client = $client;
        $this->cache = new FilesystemAdapter();
        $this->logger = $logger;
        $this->cache_ttl = $cache_ttl;
    }

    /**
     * Return a list of all projects accessible to the current user
     * If they are in cache, they are taken from there
     * @return array|mixed
     * @throws InvalidArgumentException
     */
    public function getProjects()
    {
        $cacheItem = $this->cache->getItem('gitlab.projects_lis');
        if ($cacheItem->isHit()) {
            $this->logger->debug('Reading projects list from cache');
            $projects = json_decode(
                $cacheItem->get()
            );
        } else {
            $projects = $this->getApiResult('/api/v4/projects');
            $this->logger->debug('Writing api result to cache');
            $cacheItem->set(json_encode($projects));
            $cacheItem->expiresAfter($this->cache_ttl);
            $this->cache->save($cacheItem);
        }

        return $projects;
    }

    /**
     * @param Project $project
     * @return array
     */
    public function getProjectsIssues(Project $project): array
    {
        return $this->getApiResult('/api/v4/projects/'.$project->getGitlabId().'/issues');
    }

    /**
     * @param string $uri
     * @return array
     * @throws ClientException
     */
    private function getApiResult(string $uri): array
    {
        $nextPage = 1;
        $result = [];
        while ($nextPage > 0) {
            $this->logger->debug("Requesting page $nextPage from gitlab server");
            $response = new GitlabResponseDto($this->client->get($uri, [
                'query' => [
                    'page' => $nextPage,
                    'per_page' => 100, // We use the max per page result as possible
                ]
            ]));

            foreach ($response->getArrayContent() as $item) {
                $result[] = $item;
            }

            $nextPage = $response->hasNext();
        }

        return $result;
    }

    /**
     * @throws InvalidArgumentException
     */
    public function clearProjectsCache(): void
    {
        $cacheItem = $this->cache->getItem('gitlab.projects_lis');
        if ($cacheItem->isHit()) {
            $this->logger->debug('Clearing projects cache');
            $this->cache->deleteItem('gitlab.projects_lis');
        }
    }
}
