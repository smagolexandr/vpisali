<?php

namespace AppBundle\Controller;

use AppBundle\Entity\DTO\DtoUser;
use AppBundle\Exception\JsonHttpException;
use AppBundle\Form\LoginType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Entity\User;
use Symfony\Component\Validator\ConstraintViolation;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="homepage")
     */
    public function indexAction(Request $request)
    {
        // replace this example code with whatever you need
        return $this->render('default/index.html.twig', [
            'base_dir' => realpath($this->getParameter('kernel.root_dir') . '/..') . DIRECTORY_SEPARATOR,
        ]);
    }

    /**
     * @param Request $request
     * @Route("/login", name="api_login")
     * @Method("POST")
     *
     * @return JsonResponse
     */
    public function loginAction(Request $request)
    {

        if (!$request->getContent()) {
            throw new JsonHttpException(404, 'Request body is empty');
        }
        $data = json_decode($request->getContent(), true);

        if (!$data['email']) {
            throw new JsonHttpException(404, 'Bad credentials');
        }

        $user = $this->getDoctrine()->getRepository('AppBundle:User')
            ->findOneBy(['email' => $data['email']]);

        if (!$user) {
            throw new JsonHttpException(404, 'Bad credentials');
        }

        if (!$data['plainPassword']) {
            throw new JsonHttpException(404, 'Bad credentials');
        }
        $result = $this->get('security.password_encoder')
            ->isPasswordValid($user, $data['plainPassword']);
        if (!$result) {
            throw new JsonHttpException(400, 'Bad credentials');
        }
        $token = base_convert(sha1(uniqid(mt_rand(), true)), 16, 36);
        $em = $this->getDoctrine()
            ->getManager();
        $user->setApiToken($token);
        $em->flush();
        $serializer = $this->get('serializer');
        $json = $serializer->normalize(
            $user,
            null,
            array('groups' => array('Short'))
        );
        return $this->json(
            ['user' => $json, 'X-AUTH-TOKEN' => $token]
        );
    }

    /**
     * @Route("/user", name="registration")
     * @Method("POST")
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function registrationAction(Request $request)
    {
        if (!$request->getContent()) {
            throw new JsonHttpException(404, 'Request body is empty');
        }
        $em = $this->getDoctrine()->getManager();
        $user = new User();

        $user = $this->get('serializer')->deserialize(
            $request->getContent(),
            User::class,
            'json',
            ['object_to_populate' => $user]
        );

        $errors = $this->get('validator')->validate($user);
        if ($errors->count()) {
            $outErrors = [];
            /** @var ConstraintViolation $error */
            foreach ($errors as $error) {
                $outErrors[$error->getPropertyPath()] = $error->getMessage();
            }
            throw new JsonHttpException(400, 'Bad Request', $outErrors);
        }

        $em->persist($user);
        $em->flush();

        return $this->json([
            'success' => [
                'code' => 200,
                'message' => 'Request form created.',
            ],
        ], 200);
    }
}
