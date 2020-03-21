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
 * Date: 10/11/17
 * Time: 9.28
 */

namespace App\Dto;


use Psr\Http\Message\ResponseInterface;

class GitlabResponseDto
{
    private $pageNumber, $nextPage, $prevPage, $perPage, $totalObjects, $totalPages;

    private $response;

    public function __construct(ResponseInterface $response)
    {
        $this->response = $response;

        $this->pageNumber = $this->getSingleHeader($response->getHeader('X-Page'));
        $this->nextPage = $this->getSingleHeader($response->getHeader('X-Next-Page'));
        $this->prevPage = $this->getSingleHeader($response->getHeader('X-Prev-Page'));
        $this->perPage = $this->getSingleHeader($response->getHeader('X-Per-Page'));
        $this->totalObjects = $this->getSingleHeader($response->getHeader('X-Total'));
        $this->totalPages = $this->getSingleHeader($response->getHeader('X-Total-Pages'));
    }

    public function getArrayContent()
    {
        $content = $this->response->getBody();
        $res = json_decode($content);
        if($res == null) {
            return [];
        }
        return $res;
    }

    /**
     * Check if there's another response page
     * @return int
     */
    public function hasNext()
    {
        if($this->pageNumber < $this->totalPages) {
            return $this->nextPage;
        }

        return 0;
    }

    /**
     * This method returns the first value of the array of all the header values
     * of the given case-insensitive header name.
     *
     * If the header does not appear in the message, this method return NULL
     *
     * @param string[] $values A list of values of the header
     * @return int The value of the header
     *
     */
    private function getSingleHeader($values)
    {
        if(count($values) > 0) {
            return $values[0];
        }

        return 0;
    }
}