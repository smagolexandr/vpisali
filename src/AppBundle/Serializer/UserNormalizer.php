<?php

namespace AppBundle\Serializer;

use AppBundle\Entity\Place;
use AppBundle\Entity\Review;
use AppBundle\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Registry;

use Symfony\Component\PropertyAccess\PropertyAccess;
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

class UserNormalizer extends ObjectNormalizer
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
        return $data instanceof User;
    }
    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format = null, array $context = [])
    {
        if (!$this->serializer instanceof NormalizerInterface) {
            throw new LogicException('Cannot normalize attributes because injected serializer is not a normalizer');
        }
        /** @var User $user */
        $user = &$object;
        return $this->serializer->normalize(new \ArrayObject([
            'id' => $user->getId(),
            'firstname' => $user->getFirstname(),
            'lastname' => $user->getLastname(),
            'phone' => $user->getPhone(),
            'image' => $user->getImage(),
        ]), $format, $context);
    }
    /**
     *
     * @param mixed  $data
     * @param string $class
     * @param null   $format
     * @param array  $context
     *
     * @return User
     */
    public function denormalize($data, $class, $format = null, array $context = array())
    {
        if (!$this->serializer instanceof DenormalizerInterface) {
            throw new LogicException('Cannot normalize attributes because injected serializer is not a normalizer');
        }
        /** @var User $user */
        $user = $context[ObjectNormalizer::OBJECT_TO_POPULATE];

        if (isset($data['email'])) {
            $user->setEmail($data['email']);
        }
        if (isset($data['plainPassword'])) {
            $user->setPlainPassword($data['plainPassword']);
        }
        if (isset($data['firstname'])) {
            $user->setFirstname($data['firstname']);
        }
        if (isset($data['lastname'])) {
            $user->setLastname($data['lastname']);
        }

        return $user;
    }
    /**
     * {@inheritdoc}
     */
    public function supportsDenormalization($data, $type, $format = null)
    {
        if ($type != User::class) {
            return false;
        }
        return true;
    }
}
