<?php

namespace AppBundle\Serializer;

use AppBundle\Entity\City;
use AppBundle\Entity\Place;
use AppBundle\Entity\Review;
use AppBundle\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\PropertyInfo\PropertyTypeExtractorInterface;
use Symfony\Component\Serializer\Exception\LogicException;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactory;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactoryInterface;
use Symfony\Component\Serializer\NameConverter\NameConverterInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

class ReviewNormalizer extends ObjectNormalizer
{
    /**
     * @var Registry
     */
    private $doctrine;

    /**
     * RequestNormalizer constructor.
     */
    public function __construct(ClassMetadataFactory $classMDF = null, NameConverterInterface $nameCv = null, PropertyAccessor $propAs = null,PropertyTypeExtractorInterface $propTE = null, Registry $doctrine)
    {
        parent::__construct($classMDF, $nameCv, $propAs, $propTE);
        $this->doctrine = $doctrine;
    }
    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof Review;
    }
    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format = null, array $context = [])
    {
        if (!$this->serializer instanceof NormalizerInterface) {
            throw new LogicException('Cannot normalize attributes because injected serializer is not a normalizer');
        }
        /** @var Review $review */
        $review = &$object;
        return $this->serializer->normalize(new \ArrayObject([
            'id' => $review->getId(),
            'content' => $review->getContent(),
            'user' => $review->getUser()->getLastname()." ".$review->getUser()->getFirstname(),
        ]), $format, $context);
    }
    /**
     *
     * @param mixed  $data
     * @param string $class
     * @param null   $format
     * @param array  $context
     *
     * @return Review
     */
    public function denormalize($data, $class, $format = null, array $context = array())
    {
        if (!$this->serializer instanceof DenormalizerInterface) {
            throw new LogicException('Cannot normalize attributes because injected serializer is not a normalizer');
        }
        /** @var Review $review */
        $review = $context[ObjectNormalizer::OBJECT_TO_POPULATE];
        if (isset($data['place'])) {
            $place = $this->doctrine->getRepository(Place::class)->find($data['place']);
            if ($place instanceOf Place){
                $review->setPlace($place);
            }
        }
        if (isset($data['user']))
        {
            $user = $this->doctrine->getRepository(User::class)->find($data['user']);
            if ($user instanceOf User){
                $review->setUser($user);
            }
        }
        if (isset($data['content'])) {
            $review->setContent($data['content']);
        }

        return $review;
    }
    /**
     * {@inheritdoc}
     */
    public function supportsDenormalization($data, $type, $format = null)
    {
        if ($type != Review::class) {
            return false;
        }
        return true;
    }
}
