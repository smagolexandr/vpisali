<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use AppBundle\Entity\Place;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use AppBundle\Exception\JsonHttpException;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Validator\ConstraintViolation;

class PlaceController extends Controller
{

    /**
     * @Route("/places/{city}", name="places")
     * @Method("GET")
     * @param int $city
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function selectPlacesAction($city)
    {
        $em = $this->getDoctrine()->getManager();
        $places = $em->getRepository(Place::class)->selectPlacesByCity($city);

        return $this->json(['places' => $places],200,[],[AbstractNormalizer::GROUPS => ['Short']]
        );
    }

    /**
     * @Route("/place/{id}", name="place")
     * @Method("GET")
     * @param int $id
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function selectAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $place = $em->getRepository(Place::class)->find($id);

        if (!$place) {
            throw new JsonHttpException(404, "Not Found");
        }

        return $this->json([
                'place' => $place
            ]
        );
    }

    /**
     * @Route("/places/user/{id}", name="user_places")
     * @Method("GET")
     * @param int $id
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function selectByUserAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $places = $em->getRepository(Place::class)->findby(['user' => $id]);

        return $this->json([
                'places' => $places
            ]
        );
    }


    /**
     * @Route("/place", name="new_place")
     * @Method("POST")
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function createAction(Request $request)
    {
        if (!$request->getContent()) {
            throw new JsonHttpException(404, 'Request body is empty');
        }
        $em = $this->getDoctrine()->getManager();
        $place = new Place();

        $place = $this->get('serializer')->deserialize(
            $request->getContent(),
            Place::class,
            'json',
            ['object_to_populate' => $place]
        );

        $errors = $this->get('validator')->validate($place);
        if ($errors->count()) {
            $outErrors = [];
            /** @var ConstraintViolation $error */
            foreach ($errors as $error) {
                $outErrors[$error->getPropertyPath()] = $error->getMessage();
            }
            throw new JsonHttpException(400, 'Bad Request', $outErrors);
        }

        $em->persist($place);
        $em->flush();

        return $this->json(['place' => $place]);
    }

    /**
     * @Route("/place/{id}", name="change_status")
     * @Method("PUT")
     *
     * @param $id
     * @return JsonResponse
     */
    public function changeStatusAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $place = $em->getRepository(Place::class)->find($id);

        $place->setStatus(!$place->getStatus());
        $em->flush();

        return $this->json(['place' => $place]);
    }
}
