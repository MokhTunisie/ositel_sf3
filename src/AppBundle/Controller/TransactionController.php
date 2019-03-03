<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Transaction;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Form\TransactionType;

/**
 * Transaction controller.
 *
 * @Route("transactions")
 */
class TransactionController extends Controller
{
    /**
     * Lists all Transaction entities.
     *
     * @Route("/", name="transactions", methods={"GET"})
     * @Template()
     */
    public function indexAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $field = $request->query->get('filterField', null);
        $value = $request->query->get('filterValue', null);

        $query = $em->getRepository(Transaction::class)->filterBy($field, $value, $request->query->get('sort', 'id'), $request->query->get('direction', 'asc'));

        $paginator = $this->get('knp_paginator');
        $entities = $paginator->paginate(
            $query, $request->query->get('page', 1), $this->getParameter('knp_paginator.page_range')
        );
        $deleteForm = $this->createDeleteForm();

        return [
            'entities' => $entities,
            'delete_form' => $deleteForm->createView(),
        ];
    }

    /**
     * Displays a form to create a new Transaction entity.
     *
     * @Route("/new", name="transactions_new", methods={"GET", "POST"})
     * @Template()
     */
    public function newAction(Request $request)
    {
        $transaction = new Transaction();
        $form = $this->createForm('AppBundle\Form\TransactionType', $transaction);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($transaction);
            $em->flush();

            return $this->redirectToRoute('transactions_show', ['id' => $transaction->getId()]);
        }

        return [
            'transaction' => $transaction,
            'form' => $form->createView(),
        ];
    }

    /**
     * Finds and displays a Transaction entity.
     *
     * @Route("/{id}", name="transactions_show", requirements={"id"="\d+"}, methods={"GET"})
     * @Template()
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository(Transaction::class)->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Transaction entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return [
            'entity' => $entity,
            'delete_form' => $deleteForm->createView(),
        ];
    }

    /**
     * Displays a form to edit an existing Transaction entity.
     *
     * @Route("/{id}/edit", name="transactions_edit", methods={"GET", "PUT"})
     * @Template()
     */
    public function editAction(Request $request, Transaction $transaction)
    {
        $em = $this->getDoctrine()->getManager();
        $deleteForm = $this->createDeleteForm($transaction);
        $editForm = $this->createEditForm($transaction);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $em->persist($transaction);
            $em->flush();

            return $this->redirectToRoute('transactions_show', ['id' => $transaction->getId()]);
        }

        return [
            'entity' => $transaction,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ];
    }

    /**
     * Creates a form to edit a Transaction entity.
     *
     * @param Transaction $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createEditForm(Transaction $entity)
    {
        $form = $this->createForm(TransactionType::class, $entity, [
            'action' => $this->generateUrl('transactions_edit', ['id' => $entity->getId()]),
            'method' => 'PUT',
        ]);

        return $form;
    }

    /**
     * Deletes a Transaction entity.
     *
     * @Route("/{id}", name="transactions_delete", methods={"DELETE"})
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository(Transaction::class)->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find Transaction entity.');
            }
            $this->get('session')->getFlashBag()->add('success', 'Success remove.');
            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('transactions'));
    }

    /**
     * Creates a form to delete a Transaction entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id = false)
    {
        if ($id) {
            return $this->createFormBuilder()
                ->setMethod('DELETE')
                ->setAction($this->generateUrl('transactions_delete', ['id' => $id]))
                ->getForm();
        } else {
            return $this->createFormBuilder()
                ->setMethod('DELETE')
                ->getForm();
        }
    }

    /**
     * Lists all Transaction entities of the selected month.
     *
     * @Route("/stats", name="transactions_stats", methods={"GET"})
     * @Template()
     */
    public function statsAction(Request $request)
    {
        return [];
    }

    /**
     * Lists all Transaction entities of the selected month.
     *
     * @Route("/stats-ajax", name="transactions_stats_ajax", methods={"GET"})
     */
    public function statsAjaxAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $date = new \DateTime('now');
        $month = $request->query->get('month', $date->format('m'));
        $year = $request->query->get('year', $date->format('Y'));

        $date->setDate((int) $year, (int) $month, 1);

        $dateRange = $date->format('d/m/Y').' - '.$date->format('t/m/Y');
        $query = $em->getRepository(Transaction::class)->filterBy('createdAt', $dateRange, $request->query->get('sort', 'id'), $request->query->get('direction', 'asc'), true);

        $paginator = $this->get('knp_paginator');
        $transactions = $paginator->paginate(
            $query, $request->query->get('page', 1), $this->getParameter('knp_paginator.page_range')
        );

        $totalInput = $em->getRepository(Transaction::class)->getTotalPerMonth($year, $month, true);
        $totalOutput = $em->getRepository(Transaction::class)->getTotalPerMonth($year, $month, false);
        $monthlyTreasury = $em->getRepository(Transaction::class)->calculateTreasury($year, $month);

        return $this->json([
            'status' => 200,
            'content' => $this->renderView('@App/Transaction/stats_ajax.html.twig', [
                'totalInput' => $totalInput,
                'totalOutput' => $totalOutput,
                'monthlyTreasury' => $monthlyTreasury,
                'transactions' => $transactions,
            ]),
        ]);
    }
}
