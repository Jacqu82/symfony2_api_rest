<?php

namespace AppBundle\Controller\Api;

use AppBundle\Controller\BaseController;
use AppBundle\Entity\Programmer;
use AppBundle\Form\ProgrammerType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
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
        $body = $request->getContent();
        $data = json_decode($body, true);

        $programmer = new Programmer();
        $form = $this->createForm(new ProgrammerType(), $programmer);
        //just for API
        $form->submit($data);

        $programmer->setUser($this->findUserByUsername('weaverryan'));

        $em = $this->getDoctrine()->getManager();
        $em->persist($programmer);
        $em->flush();

        $location = $this->generateUrl('api_programmers_show', ['nickname' => $programmer->getNickname()]);

        $response = new JsonResponse($this->serialize($programmer), 201);
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

        return new JsonResponse($this->serialize($programmer), 200);
    }

    /**
     * @Route("/api/programmers", name="api_programmers_list")
     * @Method("GET")
     */
    public function listAction()
    {
        /** @var Programmer[] $programmers */
        $programmers = $this->getDoctrine()->getRepository(Programmer::class)->findAll();

        $data = ['programmers' => []];
        foreach ($programmers as $programmer) {
            $data['programmers'][] = $this->serialize($programmer);
        }

        return new JsonResponse($data, 200);
    }

    private function serialize(Programmer $programmer)
    {
        return [
            'nickname' => $programmer->getNickname(),
            'avatarNumber' => $programmer->getAvatarNumber(),
            'tagLine' => $programmer->getTagLine(),
            'powerLevel' => $programmer->getPowerLevel(),
            'user' => $programmer->getUser()->getUsername()
        ];
    }
}
