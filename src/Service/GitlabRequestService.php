<?php

namespace App\Service;

use App\Client\GitlabApiClient;
use App\Dto\GitlabResponseDto;
use App\Entity\Project;
use App\Model\Issue;
use Doctrine\Common\Collections\ArrayCollection;
use GuzzleHttp\Exception\ClientException;
use JMS\Serializer\DeserializationContext;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerBuilder;
use Psr\Cache\InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Symfony\Component\Cache\Adapter\AdapterInterface;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\HttpFoundation\Request;

class GitlabRequestService
{
    public const CACHE_KEY = 'gitlab.projects_list';

    protected const GITLAB_API_BASE_PATH = '/api/v4/projects';

    /**
     * @var GitlabApiClient
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
        GitlabApiClient $client,
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
    public function getProjects()
    {
        $cacheItem = $this->cache->getItem(self::CACHE_KEY);
        if ($cacheItem->isHit()) {
            $this->logger->debug('Reading projects list from cache');
            $projects = json_decode(
                $cacheItem->get()
            );
        } else {
            $projects = $this->getApiResult(self::GITLAB_API_BASE_PATH);
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
        $apiPath = sprintf('%s/%s/issues', self::GITLAB_API_BASE_PATH, $project->getGitlabId());
        $nextPage = 1;
        $result = new ArrayCollection();
        $serializer = SerializerBuilder::create()->build();
        while ($nextPage > 0) {
            $this->logger->debug("Requesting issues page $nextPage from gitlab server");
            $response = new GitlabResponseP($this->client->request(
                Request::METHOD_GET,
                $uri,
                [
                    'query' => [
                        'page' => $nextPage,
                        'per_page' => 100, // We use the max per page result as possible
                    ]
                ]
            ));
        }
        return $this->getApiResult($apiPath);
    }

    /**
     * @param string $uri
     * @return ArrayCollection
     * @throws ClientException
     */
    private function getApiResult(string $uri): ArrayCollection
    {
        $nextPage = 1;
        $result = new ArrayCollection();
        $serializer = SerializerBuilder::create()->build();
        while ($nextPage > 0) {
            $this->logger->debug("Requesting page $nextPage from gitlab server");


            $issues = $serializer->deserialize(
                $response->getBody(),
                'array<App\Model\Issue>',
                'json'
            );

            $result->add($issues);

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
