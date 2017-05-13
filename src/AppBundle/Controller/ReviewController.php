<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Review;
use AppBundle\Exception\JsonHttpException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\ConstraintViolation;

class ReviewController extends Controller
{
    /**
     * @Route("/review", name="new_review")
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
        $review = new Review();

        $review = $this->get('serializer')->deserialize(
            $request->getContent(),
            Review::class,
            'json',
            ['object_to_populate' => $review]
        );

        $errors = $this->get('validator')->validate($review);
        if ($errors->count()) {
            $outErrors = [];
            /** @var ConstraintViolation $error */
            foreach ($errors as $error) {
                $outErrors[$error->getPropertyPath()] = $error->getMessage();
            }
            throw new JsonHttpException(400, 'Bad Request', $outErrors);
        }

        $em->persist($review);
        $em->flush();

        return $this->json([
            'success' => [
                'code' => 200,
                'message' => 'Review created.',
            ],
        ], 200);
    }
}
