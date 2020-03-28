<?php

namespace App\Tests\Service;

use App\Client\GitlabApiClient;
use App\Entity\Project;
use App\Service\GitlabRequestService;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;

class GitlabRequestServiceTest extends TestCase
{
    /**
     * @var GitlabApiClient|MockObject
     */
    protected $client;

    /**
     * @var MockObject|LoggerInterface
     */
    protected $logger;

    /**
     * @var MockObject|FilesystemAdapter
     */
    protected $cache;

    public function setUp(): void
    {
        $this->client = $this->createMock(GitlabApiClient::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->cache = $this->createMock(FilesystemAdapter::class);
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testClearProjectsCache()
    {
        $cacheItem = $this->createMock(CacheItemInterface::class);
        $cacheItem->expects($this->once())
            ->method('isHit')
            ->willReturn(true);

        $this->logger->expects($this->once())
            ->method('debug')
            ->with('Clearing projects cache');

        $this->cache->expects($this->once())
            ->method('getItem')
            ->with(GitlabRequestService::CACHE_KEY)
            ->willReturn($cacheItem);

        $this->cache->expects($this->once())
            ->method('deleteItem')
            ->with(GitlabRequestService::CACHE_KEY);

        $service = $this->getLGitlabRequestService();
        $service->clearProjectsCache();
    }

    public function testGetProjectsIssues()
    {
        $project = new Project();
        $project->setGitlabId(123456);

        $response = new Response(
            200,
            [
                'X-Page' => 1,
                'X-Next-Page' => null,
                'X-Prev-Page' => null,
                'X-Per-Page' => 20,
                'X-Total' => 1,
                'X-Total-Pages' => 1
            ],
            file_get_contents(__DIR__ . '/../MockData/get_projects_issues.json')
        );

        $this->client->expects($this->once())
            ->method('request')
            ->willReturn($response);

        $service = $this->getLGitlabRequestService();
        $service->getProjectsIssues($project);
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testGetProjectsUsingCache()
    {
        $cacheItem = $this->createMock(CacheItemInterface::class);
        $cacheItem->expects($this->once())
            ->method('isHit')
            ->willReturn(true);
        $cacheItem->expects($this->once())
            ->method('get')
            ->willReturn(file_get_contents(__DIR__ . '/../MockData/get_projects.json'));

        $this->cache->expects($this->once())
            ->method('getItem')
            ->with(GitlabRequestService::CACHE_KEY)
            ->willReturn($cacheItem);

        $this->logger->expects($this->once())
            ->method('debug')
            ->with('Reading projects list from cache');

        $service = $this->getLGitlabRequestService();
        $service->getProjects();
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testGetProjectsWithoutCache()
    {
        $cacheItem = $this->createMock(CacheItemInterface::class);
        $cacheItem->expects($this->once())
            ->method('isHit')
            ->willReturn(false);
        $cacheItem->expects($this->once())
            ->method('set');
        $cacheItem->expects($this->once())
            ->method('expiresAfter');

        $this->cache->expects($this->once())
            ->method('getItem')
            ->with(GitlabRequestService::CACHE_KEY)
            ->willReturn($cacheItem);

        $this->cache->expects($this->once())
            ->method('getItem')
            ->with(GitlabRequestService::CACHE_KEY)
            ->willReturn($cacheItem);
        $this->cache->expects($this->once())
            ->method('save')
            ->with($cacheItem);

        $this->logger->expects($this->exactly(2))
            ->method('debug')
            ->withConsecutive(
                ['Requesting page 1 from gitlab server'],
                ['Writing api result to cache']
            );

        $response = new Response(
            200,
            [
                'X-Page' => 1,
                'X-Next-Page' => null,
                'X-Prev-Page' => null,
                'X-Per-Page' => 20,
                'X-Total' => 1,
                'X-Total-Pages' => 1
            ],
            file_get_contents(__DIR__ . '/../MockData/get_projects.json')
        );

        $this->client->expects($this->once())
            ->method('request')
            ->willReturn($response);

        $service = $this->getLGitlabRequestService();
        $service->getProjects();
    }

    private function getLGitlabRequestService()
    {
        return new GitlabRequestService(
            $this->client,
            $this->logger,
            3600,
            $this->cache
        );
    }
}
