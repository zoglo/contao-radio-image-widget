<?php

declare(strict_types=1);

namespace Zoglo\RadioImageWidgetBundle\Widget;

use Contao\Input;
use Contao\StringUtil;
use Contao\System;
use Contao\Widget;
use Symfony\Component\Finder\Finder;

class RadioImage extends Widget
{
    protected $blnSubmitInput = true;

    protected $strTemplate = 'be_widget';

    protected int|null $cols = null;

    protected string $imagePath = '';

    public function __set($strKey, $varValue)
    {
        switch ($strKey) {
            case 'cols':
                if ((int) $varValue > 0) {
                    $this->cols = (int) $varValue;
                }
                break;

            case 'imagePath':
                $this->imagePath = $varValue ? rtrim((string) $varValue, '/').'/' : '';
                break;

            case 'options':
                $this->arrOptions = StringUtil::deserialize($varValue, true);
                break;

            default:
                parent::__set($strKey, $varValue);
        }
    }

    public function validate()
    {
        $varValue = $this->getPost($this->strName);

        if (!empty($varValue) && !$this->isValidOption($varValue)) {
            $this->addError($GLOBALS['TL_LANG']['ERR']['invalid']);
        }

        parent::validate();
    }

    public function generate()
    {
        if (!$this->arrOptions) {
            return '';
        }

        $objContainer = System::getContainer();
        $strProjectDir = $objContainer->getParameter('kernel.project_dir');

        $directories = array_filter([
            $strProjectDir. '/' . $this->imagePath,
            $objContainer->getParameter('contao.web_dir'). '/' . $this->imagePath,
        ], 'is_dir');

        $fallback = empty($this->varValue) && !Input::isPost();
        $arrOptions = [];

        foreach ($this->arrOptions as $arrOption) {
            $strValue = (string) ($arrOption['value'] ?? '');

            $arrOptions[] = [
                'value' => $strValue,
                'label' => $arrOption['label'] ?? $strValue,
                'image' => $this->findImage($strValue, $directories),
                'checked' => $fallback ? !$arrOptions : (bool) $this->isChecked($arrOption),
            ];
        }

        return $objContainer->get('twig')->render('@Contao/backend/widget/radio_image.html.twig', [
            'id' => $this->strId,
            'name' => $this->strName,
            'class' => $this->strClass,
            'cols' => $this->cols ?? \count($arrOptions),
            'options' => $arrOptions,
            'attributes' => $this->getAttributes(),
        ]);
    }

    private function findImage(string $strValue, array $directories): string
    {
        if (!$strValue || !$directories) {
            return '';
        }

        foreach (['svg', 'png'] as $strExtension) {
            foreach (Finder::create()->files()->depth(0)->in($directories)->name($strValue.'.'.$strExtension) as $objFile) {
                return $this->imagePath.$objFile->getFilename();
            }
        }

        return '';
    }
}
