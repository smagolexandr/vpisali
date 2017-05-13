<?php

namespace AppBundle\Serializer;

use AppBundle\Entity\City;
use AppBundle\Entity\Place;
use AppBundle\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\PropertyInfo\PropertyTypeExtractorInterface;
use Symfony\Component\Serializer\Exception\LogicException;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactoryInterface;
use Symfony\Component\Serializer\NameConverter\NameConverterInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

class PlaceNormalizer extends ObjectNormalizer
{
    /**
     * @var Registry
     */
    private $doctrine;

    /**
     * RequestNormalizer constructor.
     *
     * @param ClassMetadataFactoryInterface|null  $classMDF
     * @param NameConverterInterface|null         $nameCv
     * @param PropertyAccessorInterface|null      $propAs
     * @param PropertyTypeExtractorInterface|null $propTE
     */
    public function __construct($classMDF, $nameCv, $propAs, $propTE, Registry $doctrine)
    {
        parent::__construct($classMDF, $nameCv, $propAs, $propTE);
        $this->doctrine = $doctrine;
    }
    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof Place;
    }
    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format = null, array $context = [])
    {
        if (!$this->serializer instanceof NormalizerInterface) {
            throw new LogicException('Cannot normalize attributes because injected serializer is not a normalizer');
        }
        /** @var Place $place */
        $place = &$object;
        return $this->serializer->normalize(new \ArrayObject([
            'id' => $place->getId(),
            'description' => $place->getDescription(),
            'longitude' => $place->getLongitude(),
            'latitude' => $place->getLatitude(),
            'address' => $place->getAddress(),
            'status' => $place->getStatus(),
            'user' => $place->getUser() instanceOf User ? $place->getUser()->getId()  : null,
            'city' => $place->getCity() instanceof City ? $place->getCity()->getName() : null,
            'reviews' => $place->getReviews()
        ]), $format, $context);
    }
    /**
     *
     * @param mixed  $data
     * @param string $class
     * @param null   $format
     * @param array  $context
     *
     * @return Place
     */
    public function denormalize($data, $class, $format = null, array $context = array())
    {
        if (!$this->serializer instanceof DenormalizerInterface) {
            throw new LogicException('Cannot normalize attributes because injected serializer is not a normalizer');
        }
        /** @var Place $place */
        $place = $context[ObjectNormalizer::OBJECT_TO_POPULATE];

        if (isset($data['description'])) {
            $place->setDescription($data['description']);
        }
        if (isset($data['latitude'])) {
            $place->setLatitude($data['latitude']);
        }
        if (isset($data['longitude'])) {
            $place->setLongitude($data['longitude']);
        }
        if (isset($data['address'])) {
            $place->setAddress($data['address']);
        }
        if (isset($data['user']))
        {
            $user = $this->doctrine->getRepository(User::class)->find($data['user']);
            if ($user instanceOf User){
                $place->setUser($user);
            }
        }
        if (isset($data['city'])) {
            $city = $this->doctrine->getRepository(City::class)->findOneBy(['name' => $data['city']]);
            if ($city instanceOf City){
                $place->setCity($city);
            }
        }
        return $place;
    }
    /**
     * {@inheritdoc}
     */
    public function supportsDenormalization($data, $type, $format = null)
    {
        if ($type != Place::class) {
            return false;
        }
        return true;
    }
}
