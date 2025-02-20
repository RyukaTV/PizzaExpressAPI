<?php

namespace App\Service;

use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\NameConverter\CamelCaseToSnakeCaseNameConverter;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use App\Entity\Produit;

class JsonConverter {

    public function encodeToJson($data) {
        $encoders = [new JsonEncoder()];
		$normalizers = [new ObjectNormalizer(null, new CamelCaseToSnakeCaseNameConverter())];

		$serializer = new Serializer($normalizers, $encoders);

        $jsonContent = $serializer->serialize($data, 'json', [
            'circular_reference_handler' => function ($object) {
                if ($object instanceof Produit) {
                    return $object->getName();
                }
                return $object->getId();
            }
        ]);

        return $jsonContent;
    }

    public function decodeFromJSon($data, $className) {
        $encoders = [new JsonEncoder()];
		$normalizers = [new ObjectNormalizer(null, new CamelCaseToSnakeCaseNameConverter())];

		$serializer = new Serializer($normalizers, $encoders);
        $object = $serializer->deserialize($data, $className, 'json');
        return $object;
    }
}
    