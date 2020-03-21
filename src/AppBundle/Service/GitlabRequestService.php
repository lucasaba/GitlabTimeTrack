<?php
/**
 * Questo file fa parte del progetto gitlab-timetrack.
 * Il codice è fornito senza alcuna garanzia e distribuito
 * con licenza di tipo open source.
 * Per le informazioni sui diritti e le informazioni sulla licenza
 * consultare il file LICENSE che deve essere distribuito
 * insieme a questo codice.
 *
 * (c) Luca Saba <lucasaba@gmail.com>
 *
 * Created by PhpStorm.
 * User: luca
 * Date: 13/11/17
 * Time: 21.06
 */

namespace AppBundle\Service;


use AppBundle\Entity\GitlabResponse;
use AppBundle\Entity\Project;
use GuzzleHttp\Client;
use Monolog\Logger;
use Symfony\Component\Cache\Simple\FilesystemCache;

class GitlabRequestService
{
    /**
     * @var Client
     */
    private $client;

    /**
     * @var FilesystemCache
     */
    private $cache;

    private $cache_ttl;

    /**
     * @var Logger
     */
    private $logger;

    public function __construct(Client $client, Logger $logger, $cache_ttl = 3600)
    {
        $this->client = $client;
        $this->cache = new FilesystemCache();
        $this->logger = $logger;
        $this->cache_ttl = $cache_ttl;
    }

    /**
     * Return a list of all projects accessible to the current user
     * If they are in cache, they are taken from there
     * @return array|mixed
     */
    public function getProjects()
    {
        if($this->cache->has('gitlab.projects_list')) {
            $this->logger->debug('Reading projects list from cache');
            $projects = json_decode(
                $this->cache->get('gitlab.projects_list', [])
            );
        } else {
            $projects = $this->getApiResult('projects');
            $this->logger->debug('Writing api result to cache');
            $this->cache->set('gitlab.projects_list', json_encode($projects), $this->cache_ttl);
        }

        return $projects;
    }

    public function getProjectsIssues(Project $project)
    {
        $items = $this->getApiResult('projects/'.$project->getGitlabId().'/issues');

        return $items;
    }

    private function getApiResult($uri)
    {
        $nextPage = 1;
        $result = [];
        while ($nextPage > 0) {
            $this->logger->debug("Requesting page $nextPage from gitlab server");
            $response = new GitlabResponse($this->client->get($uri, [
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
}
