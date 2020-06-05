<?php

namespace App\Controller;

use App\Entity\Issue;
use App\Entity\Milestone;
use App\Entity\Note;
use App\Entity\Project;
use App\Form\Type\ChooseMilestonesType;
use App\Form\Type\ChooseProjectsType;
use App\Service\Chart\Chart;
use App\Service\GitlabRequestService;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectRepository;
use Exception;
use GuzzleHttp\Exception\ClientException;
use Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse;
use Knp\Snappy\Pdf;
use Psr\Cache\InvalidArgumentException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MilestoneController extends AbstractController
{
    /** @var EntityManagerInterface */
    private $entityManager;

    /** @var GitlabRequestService */
    private $gitlabRequestService;

    /** @var Pdf */
    private $pdf;

    private $chart;

    private $issueInserted;
    private $issueUpdated;
    private $milestoneInserted;
    private $milestoneUpdated;

    public function __construct(
        EntityManagerInterface $entityManager,
        GitlabRequestService $gitlabRequestService,
        Chart $chart,
        Pdf $pdf
    ) {
        $this->entityManager = $entityManager;
        $this->gitlabRequestService = $gitlabRequestService;
        $this->chart = $chart;
        $this->pdf = $pdf;
    }


    /**
     * @Route("/update-milestones/{id}", name="update_milestones")
     */
    public function refreshMilestonesAction(Project $project, Request $request): Response
    {
        $this->updateMilestones($project);

        if ($this->milestoneUpdated > 0) {
            $this->addFlash('success', "$this->milestoneUpdated issues have been updated.");
        }

        if ($this->issueInserted > 0) {
            $this->addFlash('success', "$this->milestoneInserted issues have been added.");
        }


        return $this->redirectToRoute('view_project', [
            'id' => $project->getId(),
        ]);
    }

    /**
     * @Route("/milestone/{id}/view", name="view_milestone")
     */
    public function viewMilestoneAction(Milestone $milestone): Response
    {
        $issues = $this->entityManager->getRepository(Issue::class)
            ->findIssuesRelatedToMilestone($milestone);

        return $this->render('default/view_milestone_issues.html.twig', [
            'issues' => $issues,
            'milestone' => $milestone,
            'spendByDate' => $this->chart->timeSpendByDate(...$issues)
        ]);
    }

    /**
     * @Route("/milestone/{id}/update-issues", name="update_milestone_issues")
     */
    public function updateMilestoneIssues(Milestone $milestone): Response
    {
        $this->updateIssues($milestone->getProject(), $milestone);

        if ($this->issueUpdated > 0) {
            $this->addFlash('success', "$this->issueUpdated issues have been updated.");
        }

        if ($this->issueInserted > 0) {
            $this->addFlash('success', "$this->issueInserted issues have been added.");
        }

        return $this->redirectToRoute('view_milestone', ['id' => $milestone->getId()]);
    }

    private function gitlabTimeToW3CTime(string $gitlabTime): DateTime
    {
        /**
         * Gitlab uses RFC3339_EXTENDED date format
         * 'Y-m-d\TH:i:s.vP'
         * We have to transform it into a much simplier ('Y-m-d\TH:i:s') format
         */
        $date = new DateTime($gitlabTime);
        return new DateTime($date->format('Y-m-d\TH:i:s'));
    }

    private function updateIssues(Project $project, ?Milestone $milestone): void
    {
        $gitlabProjectIssues = $this->gitlabRequestService->getProjectsIssues($project, $milestone);

        $this->issueUpdated = 0;
        $this->issueInserted = 0;
        foreach ($gitlabProjectIssues as $gitlabProjectIssue) {
            $issue = $this->updateIssue($project, $gitlabProjectIssue);
            $this->updateNotes($issue);
            $this->entityManager->flush();
        }
    }

    private function updateIssue(Project $project, $gitlabIssue): Issue
    {
        $issue = $this->entityManager->getRepository(Issue::class)
            ->findOneBy(['gitlabId' => $gitlabIssue->id]);

        if ($issue === null) {
            $issue = new Issue(
                $gitlabIssue->title,
                $gitlabIssue->id,
                $gitlabIssue->iid,
                $project,
                $this->gitlabTimeToW3CTime($gitlabIssue->created_at),
                $this->gitlabTimeToW3CTime($gitlabIssue->updated_at),
                $gitlabIssue->state,
                $gitlabIssue->time_stats->time_estimate,
                $gitlabIssue->time_stats->total_time_spent
            );

            $this->issueInserted++;
        } elseif ($issue instanceof Issue) {
            $lastUpdated = $this->gitlabTimeToW3CTime($gitlabIssue->updated_at);
            $updatedAt = $issue->getUpdatedAt();
            if ($updatedAt && $lastUpdated->getTimestamp() > $updatedAt->getTimestamp()) {
                $issue->update(
                    $gitlabIssue->state,
                    new DateTime($gitlabIssue->updated_at),
                    $gitlabIssue->time_stats->time_estimate,
                    $gitlabIssue->time_stats->total_time_spent
                );
                $this->issueUpdated++;
            }
        }

        $milestone = null;
        if ($gitlabIssue->milestone !== null) {
            $milestone = $this->entityManager->getRepository(Milestone::class)
                ->findOneBy(['gitlabId' => $gitlabIssue->milestone->id]);
        }
        $issue->setMilestone($milestone);

        $this->entityManager->persist($issue);

        return $issue;
    }

    private function updateNotes(Issue $issue): void
    {
        $gitlabIssuesNotes = $this->gitlabRequestService->getIssueNotes($issue);
        foreach ($gitlabIssuesNotes as $gitlabIssuesNote) {
            $note = $this->entityManager->getRepository(Note::class)
                ->findOneBy(['gitlabId' => $gitlabIssuesNote->id]);
            if ($note === null) {
                $note = new Note(
                    $gitlabIssuesNote->id,
                    $issue,
                    $gitlabIssuesNote->body,
                    $gitlabIssuesNote->author->id,
                    $gitlabIssuesNote->system,
                    $this->gitlabTimeToW3CTime($gitlabIssuesNote->created_at),
                    $this->gitlabTimeToW3CTime($gitlabIssuesNote->updated_at));
            } else {
                $lastUpdated = $this->gitlabTimeToW3CTime($gitlabIssuesNote->updated_at);
                $updatedAt = $note->getUpdatedAt();
                if ($updatedAt && $lastUpdated->getTimestamp() > $updatedAt->getTimestamp()) {
                    $note->setBody($gitlabIssuesNote->body);
                    $note->setAuthor($gitlabIssuesNote->author->id);
                    $note->setSystem($gitlabIssuesNote->system);
                    $note->setUpdatedAt($this->gitlabTimeToW3CTime($gitlabIssuesNote->updated_at));
                }
            }
            $this->entityManager->persist($note);
        }
    }

    private function updateMilestones(Project $project): void
    {
        $gitlabMilestones = $this->gitlabRequestService->getProjectsMilestones($project);

        foreach ($gitlabMilestones as $gitlabMilestone) {
            $milestone = $this->entityManager->getRepository(Milestone::class)
                ->findOneBy(['gitlabId' => $gitlabMilestone->id]);

            if ($milestone === null) {
                $milestone = new Milestone(
                    $gitlabMilestone->id,
                    $project,
                    $gitlabMilestone->title,
                    $this->gitlabTimeToW3CTime($gitlabMilestone->created_at),
                    $this->gitlabTimeToW3CTime($gitlabMilestone->updated_at),
                    $gitlabMilestone->due_date ? $this->gitlabTimeToW3CTime($gitlabMilestone->due_date) : null,
                    $gitlabMilestone->start_date ? $this->gitlabTimeToW3CTime($gitlabMilestone->start_date) : null,
                    $gitlabMilestone->state,
                    $gitlabMilestone->description
                );
                $this->entityManager->persist($milestone);
                $this->milestoneInserted++;
            } elseif ($milestone instanceof Milestone) {
                $lastUpdated = $this->gitlabTimeToW3CTime($gitlabMilestone->updated_at);
                $updatedAt = $milestone->getUpdatedAt();
                if ($updatedAt && $lastUpdated->getTimestamp() > $updatedAt->getTimestamp()) {
                    $milestone->update(
                        $gitlabMilestone->state,
                        $gitlabMilestone->title,
                        $gitlabMilestone->description,
                        $this->gitlabTimeToW3CTime($gitlabMilestone->updated_at),
                        $this->gitlabTimeToW3CTime($gitlabMilestone->due_date),
                        $this->gitlabTimeToW3CTime($gitlabMilestone->start_date)
                    );
                    $this->milestoneUpdated++;
                }
                $this->entityManager->persist($milestone);
            }
        }

        $this->entityManager->flush();
    }
}
