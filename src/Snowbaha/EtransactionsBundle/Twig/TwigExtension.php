<?php

namespace Snowbaha\EtransactionsBundle\Twig;

use \Twig_Extension;
use \Twig_SimpleFunction;
use \Twig_Environment;


/**
 * Class TwigExtension
 * @package Snowbaha\EtransactionsBundle\Twig
 */
class TwigExtension extends Twig_Extension
{
    /**
     * @return array
     */
    public function getFunctions()
    {
        return array(
            new Twig_SimpleFunction(
                'paiementForm',
                array($this, 'paiementForm'),
                array(
                    'is_safe' => array('html'),
                    'needs_environment' => true
                )
            ),
        );
    }

    /**
     * @param Twig_Environment $twig  // allow to use the render (param in getFunctions() are very important!)
     * @param $fields
     * @return mixed
     */
    public function paiementForm(Twig_Environment $twig, $fields)
    {
        $form_html = $twig->render('SnowbahaEtransactionsBundle:Etransactions:form.html.twig', array('fields' => $fields));

        return $form_html;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'snowbaha_etransactions_twig_extension';
    }
}
