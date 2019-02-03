<?php

namespace AppBundle\Controller\Api;

use AppBundle\Controller\BaseController;
use AppBundle\Entity\Programmer;
use AppBundle\Form\ProgrammerType;
use AppBundle\Form\UpdateProgrammerType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class ProgrammerController extends BaseController
{
    /**
     * @Route("/api/programmers")
     * @Method("POST")
     */
    public function newAction(Request $request)
    {
        $programmer = new Programmer();

        $form = $this->createForm(new ProgrammerType(), $programmer);
        $this->processForm($request, $form);

        $programmer->setUser($this->findUserByUsername('weaverryan'));

        $em = $this->getDoctrine()->getManager();
        $em->persist($programmer);
        $em->flush();

        $location = $this->generateUrl('api_programmers_show', ['nickname' => $programmer->getNickname()]);

        $response = $this->createApiResponse($programmer, 201);
        $response->headers->set('Location', $location);

        return $response;
    }

    /**
     * @Route("/api/programmers/{nickname}", name="api_programmers_show")
     * @Method("GET")
     */
    public function showAction($nickname)
    {
        /** @var Programmer $programmer */
        $programmer = $this->getDoctrine()->getRepository(Programmer::class)->findOneBy(['nickname' => $nickname]);
        if (!$programmer) {
            throw $this->createNotFoundException('Programmer has gone!');
        }

        return $this->createApiResponse($programmer);
    }

    /**
     * @Route("/api/programmers", name="api_programmers_list")
     * @Method("GET")
     */
    public function listAction()
    {
        /** @var Programmer[] $programmers */
        $programmers = $this->getDoctrine()->getRepository(Programmer::class)->findAll();

        $data = ['programmers' => $programmers];

//        $data = ['programmers' => []];
//        foreach ($programmers as $programmer) {
//            $data['programmers'][] = $this->serialize($programmer);
//        }

        return $this->createApiResponse($data);
    }

    /**
     * @Route("/api/programmers/{nickname}", name="api_programmers_update")
     * @Method({"PUT", "PATCH"})
     */
    public function updateAction($nickname, Request $request)
    {
        /** @var Programmer $programmer */
        $programmer = $this->getDoctrine()->getRepository(Programmer::class)->findOneBy(['nickname' => $nickname]);
        if (!$programmer) {
            throw $this->createNotFoundException('Programmer has gone!');
        }

        $form = $this->createForm(new UpdateProgrammerType(), $programmer);
        $this->processForm($request, $form);

        $em = $this->getDoctrine()->getManager();
        //$em->persist($programmer);
        $em->flush();

        return $this->createApiResponse($programmer);
    }

    /**
     * @Route("/api/programmers/{nickname}", name="api_programmers_delete")
     * @Method("DELETE")
     */
    public function deleteAction($nickname)
    {
        /** @var Programmer $programmer */
        $programmer = $this->getDoctrine()->getRepository(Programmer::class)->findOneBy(['nickname' => $nickname]);
        if (!$programmer) {
            throw $this->createNotFoundException('Programmer has gone, There is nothing to delete!');
        } else {
            $em = $this->getDoctrine()->getManager();
            $em->remove($programmer);
            $em->flush();
        }

        return new JsonResponse(null, 204);
    }

    public function processForm(Request $request, FormInterface $form)
    {
        $data = json_decode($request->getContent(), true);
        $clearMissing = $request->getMethod() !== 'PATCH';

        //just for API
        $form->submit($data, $clearMissing);
    }
}
