<?php

namespace Snowbaha\EtransactionsBundle\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use Twig\Environment;

/**
 * Class TwigExtension
 * @package Snowbaha\EtransactionsBundle\Twig
 */
class TwigExtension extends AbstractExtension
{
    /**
     * @return array
     */
    public function getFunctions()
    {
        return [
            new TwigFunction(
                'paiementForm',
                [$this, 'paiementForm'],
                [
                    'is_safe' => ['html'],
                    'needs_environment' => true
                ]
            ),
        ];
    }

    /**
     * @param Environment $twig  // allow to use the render (param in getFunctions() are very important!)
     * @param $fields
     * @return mixed
     */
    public function paiementForm(Environment $twig, $fields)
    {
        $form_html = $twig->render('SnowbahaEtransactionsBundle:Etransactions:form.html.twig', ['fields' => $fields]);

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
