<?php

namespace App\Service;

use App\Dto\GitlabResponseDto;
use App\Entity\Issue;
use App\Entity\Project;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Psr\Cache\InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Symfony\Component\Cache\Adapter\AdapterInterface;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\HttpFoundation\Request;

class GitlabRequestService
{
    public const CACHE_KEY = 'gitlab.projects_list';

    /**
     * @var Client
     */
    private $client;

    /**
     * @var AdapterInterface
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

    public function __construct(
        Client $client,
        LoggerInterface $logger,
        int $cache_ttl = 3600,
        AdapterInterface $cache = null
    ) {
        $this->client = $client;
        if (null === $cache) {
            $this->cache = new FilesystemAdapter();
        } else {
            $this->cache = $cache;
        }

        $this->logger = $logger;
        $this->cache_ttl = $cache_ttl;
    }

    /**
     * Return a list of all projects accessible to the current user
     * If they are in cache, they are taken from there
     * @return array|mixed
     * @throws InvalidArgumentException
     */
    public function getProjects(?string $visibility)
    {
        $cacheItem = $this->cache->getItem(self::CACHE_KEY);
        if ($cacheItem->isHit()) {
            $this->logger->debug('Reading projects list from cache');
            $projects = json_decode(
                $cacheItem->get()
            );
        } else {
            $projects = $this->getApiResult('/api/v4/projects', $visibility);
            $this->logger->debug('Writing api result to cache');
            $cacheItem->set(json_encode($projects));
            $cacheItem->expiresAfter($this->cache_ttl);
            $this->cache->save($cacheItem);
        }

        return $projects;
    }

    public function getProjectMilestones(Project $project)
    {
        $cacheItem = $this->cache->getItem(self::CACHE_KEY);
        if ($cacheItem->isHit()) {
            $this->logger->debug('Reading project milestones list from cache');
            $milestones = json_decode(
                $cacheItem->get()
            );
        } else {
            $milestones = $this->getApiResult('/api/v4/projects/' . $project->getGitlabId() . '/milestones');
            $this->logger->debug('Writing api result to cache');
            $cacheItem->set(json_encode($milestones));
            $cacheItem->expiresAfter($this->cache_ttl);
            $this->cache->save($cacheItem);
        }

        return $milestones;
    }

    public function getProjectsIssues(Project $project): array
    {
        return $this->getApiResult('/api/v4/projects/'.$project->getGitlabId().'/issues');
    }

    public function getProjectsMilestones(Project $project): array
    {
        return $this->getApiResult('/api/v4/projects/'.$project->getGitlabId().'/milestones');
    }

    /**
     * @param Project $project
     * @return array
     */
    public function getIssueNotes(Issue $issue): array
    {
        return $this->getApiResult('/api/v4/projects/'.$issue->getProject()->getGitlabId().
            '/issues/'.$issue->getIssueNumber().'/notes');
    }

    /**
     * @param string $uri
     * @return array
     * @throws ClientException
     */
    private function getApiResult(string $uri, ?string $visibility = null): array
    {
        $nextPage = 1;
        $result = [];
        while ($nextPage > 0) {
            $this->logger->debug("Requesting page $nextPage from gitlab server");
            $response = new GitlabResponseDto($this->client->request(
                Request::METHOD_GET,
                $uri,
                [
                    'query' => [
                        'visibility' => $visibility,
                        'page' => $nextPage,
                        'per_page' => 100, // We use the max per page result as possible
                    ]
                ]
            ));

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
        $cacheItem = $this->cache->getItem(self::CACHE_KEY);
        if ($cacheItem->isHit()) {
            $this->logger->debug('Clearing projects cache');
            $this->cache->deleteItem(self::CACHE_KEY);
        }
    }
}
