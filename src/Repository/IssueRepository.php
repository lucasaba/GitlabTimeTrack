<?php

namespace App\Repository;

use App\Entity\Issue;
use App\Entity\Project;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;

/**
 * @method Issue|null find($id, $lockMode = null, $lockVersion = null)
 * @method Issue|null findOneBy(array $criteria, array $orderBy = null)
 * @method Issue[]    findAll()
 * @method Issue[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class IssueRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Issue::class);
    }

    /**
     * @param Project $project
     * @return mixed
     */
    public function findTimedIssuesRelatedToProject(Project $project)
    {
        return $this->getEntityManager()->createQuery(
            'SELECT i FROM App:Issue i 
             WHERE i.project = :project 
             AND (i.timeEstimate > 0 OR i.totalTimeSpent > 0)
             ORDER BY i.gitlabId DESC'
        )->setParameter('project', $project)->getResult();
    }

    /**
     * Returns the number of issues monitored by GitlabTimeTrack
     *
     * @return int|mixed
     */
    public function countIssues()
    {
        $qry = $this->createQueryBuilder('i')
            ->select('COUNT(i.id)')
            ->getQuery();

        try {
            $result = $qry->getSingleScalarResult();
        } catch (NoResultException $e) {
            return 0;
        } catch (NonUniqueResultException $e) {
            return 0;
        }

        return $result;
    }

    /**
     * Returns the number of minutes spent on all issues
     *
     * @return int|mixed
     */
    public function countTimeSpent()
    {
        $qry = $this->createQueryBuilder('i')
            ->select('SUM(i.totalTimeSpent)')
            ->getQuery();

        try {
            $result = $qry->getSingleScalarResult();
        } catch (NoResultException $e) {
            return 0;
        } catch (NonUniqueResultException $e) {
            return 0;
        }

        return $result;
    }

    /**
     * Returns the estimated number of minutes to be spent on all issues
     *
     * @return int|mixed
     */
    public function countEstimatedTime()
    {
        $qry = $this->createQueryBuilder('i')
            ->select('SUM(i.timeEstimate)')
            ->getQuery();

        try {
            $result = $qry->getSingleScalarResult();
        } catch (NoResultException | NonUniqueResultException $e) {
            return 0;
        }

        return $result;
    }
}
