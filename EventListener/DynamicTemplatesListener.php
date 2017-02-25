<?php

namespace Rik\DynamicTemplatesBundle\EventListener;

use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;

class DynamicTemplatesListener {

    protected $templating;

    public function __construct(EngineInterface $templating) {
        $this->templating = $templating;
    }

    public function onKernelRequest(GetResponseEvent $event) {
        $attrs = $event->getRequest()->attributes;

        if($attrs->get('_controller') == 'Symfony\Bundle\FrameworkBundle\Controller\TemplateController::templateAction') {
            $template = $attrs->get('template');

            preg_match_all('/{(\w+)}+/', $template, $matches);

            if(!empty($matches['1'])) {
                $replace = [];

                foreach($matches['1'] as $match) {
                    if($attrs->has($match)) {
                        $replace['{' . $match . '}'] = $attrs->get($match);
                    }
                }

                if(!empty($replace)) $template = strtr($template, $replace);
            }

            $response = $this->templating->render($template);

            $event->setResponse(new Response($response));
        }
    }

}