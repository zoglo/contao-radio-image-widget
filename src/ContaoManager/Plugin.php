<?php

declare(strict_types=1);

namespace Zoglo\RadioImageWidgetBundle\ContaoManager;

use Contao\CoreBundle\ContaoCoreBundle;
use Contao\ManagerPlugin\Bundle\BundlePluginInterface;
use Contao\ManagerPlugin\Bundle\Config\BundleConfig;
use Contao\ManagerPlugin\Bundle\Parser\ParserInterface;
use Zoglo\RadioImageWidgetBundle\ZogloRadioImageWidgetBundle;

class Plugin implements BundlePluginInterface
{
    public function getBundles(ParserInterface $parser): array
    {
        return [
            (new BundleConfig(ZogloRadioImageWidgetBundle::class))
                ->setLoadAfter([ContaoCoreBundle::class]),
        ];
    }
}
