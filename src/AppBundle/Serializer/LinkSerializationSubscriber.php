<?php

namespace AppBundle\Serializer;

use AppBundle\Annotation\Link;
use Doctrine\Common\Annotations\Reader;
use JMS\Serializer\EventDispatcher\EventSubscriberInterface;
use JMS\Serializer\EventDispatcher\ObjectEvent;
use JMS\Serializer\JsonSerializationVisitor;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Symfony\Component\Routing\RouterInterface;

class LinkSerializationSubscriber implements EventSubscriberInterface
{
    private $router;
    private $annotationReader;
    private $expressionLanguage;

    public function __construct(RouterInterface $router, Reader $annotationReader)
    {
        $this->router = $router;
        $this->annotationReader = $annotationReader;
        $this->expressionLanguage = new ExpressionLanguage();
    }

    public static function getSubscribedEvents()
    {
        return [
            [
                'event' => 'serializer.post_serialize',
                'method' => 'onPostSerialize',
                'format' => 'json'
            ]
        ];
    }

    public function onPostSerialize(ObjectEvent $event)
    {
        /** @var JsonSerializationVisitor $visitor */
        $visitor = $event->getVisitor();

        $object = $event->getObject();
        $annotations = $this->annotationReader->getClassAnnotations(new \ReflectionClass($object));
        $links = [];

        foreach ($annotations as $annotation) {
            if ($annotation instanceof Link) {
                $uri = $this->router->generate(
                    $annotation->route,
                    $this->resolveParams($annotation->params, $object)
                );
                $links[$annotation->name] = $uri;
            }
        }

        if ($links) {
            $visitor->addData('_links', $links);
        }

//        $programmer = $event->getObject();
//
//        $uri = $this->router->generate('api_programmers_show', ['nickname' => $programmer->getNickname()]);
//        $visitor->addData('uri', $uri);
    }

    private function resolveParams(array $params, $object): array
    {
        foreach ($params as $key => $param) {
            $params[$key] = $this->expressionLanguage
                ->evaluate($param, ['object' => $object]);
        }

        return $params;
    }
}
