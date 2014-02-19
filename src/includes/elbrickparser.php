<?php

/**
 * @package Abricos
 * @subpackage EShop
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @author Alexander Kuzmin <roosit@abricos.org>
 */
class EShopElementBrickParser {

    /**
     * @var CatalogElement
     */
    public $element;

    public function __construct(CatalogElement $el) {
        $this->element = $el;
    }


    public function Parse(Ab_CoreBrick $brick) {

        $replace = $this->ParseOptions($brick);
        $brick->content = Brick::ReplaceVarByData($brick->content, $replace);

        $replace = $this->ParseOptionGroups($brick);
        $brick->content = Brick::ReplaceVarByData($brick->content, $replace);
    }


    /**
     * Получить значение опции элемента
     *
     * @param string $sOption имя опции
     */
    public function GetOptionValue($sOption) {
        $elOptBase = $this->element->detail->optionsBase; // значение базовых опций
        $elOptPers = $this->element->detail->optionsPers; // значения персональных опций

        if (!empty($elOptBase[$sOption])) {
            return $elOptBase[$sOption];
        } else if (!empty($elOptPers[$sOption])) {
            return $elOptPers[$sOption];
        }
        return "";
    }

    public function ParseOptionGroups(Ab_CoreBrick $brick) {
        $p = & $brick->param->param;
        $v = & $brick->param->var;
        $el = $this->element;
        $replace = array();

        $cMan = EShopModule::$instance->GetManager()->cManager;

        $optionGroupList = $cMan->ElementOptionGroupList();
        $elTypeList = $cMan->ElementTypeList();

        $aOptionGroups = explode(",", $v['optiongroups']);

        foreach ($aOptionGroups as $sOptGroup) {
            $sOptGroup = trim($sOptGroup);
            $tpOptGroup = $v['optiongroup-'.$sOptGroup];
            $tpOptGRow = $v['optiongrouprow-'.$sOptGroup];
            if (empty($sOptGroup) || empty($tpOptGroup) || empty($tpOptGRow)) {
                continue;
            }

            $replace['optiongroup-'.$sOptGroup] = "";

            $optionGroup = $optionGroupList->GetByName($sOptGroup);
            if (empty($optionGroup)) {
                continue;
            }

            $lst = "";
            for ($i = 0; $i < 2; $i++) {
                if ($i == 0) {
                    $elType = $elTypeList->Get(0);
                } else if ($el->elTypeId > 0) {
                    $elType = $elTypeList->Get($el->elTypeId);
                } else {
                    continue;
                }

                for ($ii = 0; $ii < $elType->options->Count(); $ii++) {
                    $option = $elType->options->GetByIndex($ii);

                    if ($option->groupid != $optionGroup->id) {
                        continue;
                    }

                    $value = $this->GetOptionValue($option->name);
                    if (empty($value)) {
                        continue;
                    }

                    $lst .= Brick::ReplaceVarByData($tpOptGRow, array(
                        "tl" => "{v#fldtl_".$option->name."}",
                        "vl" => "{v#fld_".$option->name."}"
                    ));
                }
            }

            $replace['optiongroup-'.$sOptGroup] = Brick::ReplaceVarByData($tpOptGroup, array(
                "rows" => $lst
            ));
        }
        return $replace;
    }

    public function ParseOptions(Ab_CoreBrick $brick) {
        $p = & $brick->param->param;
        $v = & $brick->param->var;
        $el = $this->element;
        $replace = array();

        $elOptBase = $el->detail->optionsBase; // значение базовых опций
        $elOptPers = $el->detail->optionsPers; // значения персональных опций

        $aOptions = explode(",", $v['options']);
        foreach ($aOptions as $sOption) {
            $sOption = trim($sOption);
            if (empty($sOption)) {
                continue;
            }

            $replace['option-'.$sOption] = "";
            $value = "";
            if (!empty($elOptBase[$sOption])) {
                $value = $elOptBase[$sOption];
            } else if (!empty($elOptPers[$sOption])) {
                $value = $elOptPers[$sOption];
            }

            if (empty($value) || empty($v['option-'.$sOption])
                || $value == '0.00' // временно (для отключения опций с плавающей точкой)
            ) {
                continue;
            }

            $replace['option-'.$sOption] = $v['option-'.$sOption];
        }

        return $replace;
    }

    public function GetReplaceData() {
        $el = $this->element;

        $cMan = EShopModule::$instance->GetManager()->cManager;
        $elTypeList = $cMan->ElementTypeList();

        $replace = array(
            "link" => $el->URI(),
            "elementid" => $el->id,
            "title" => $el->title,
            "name" => $el->name,
            "cattitle" => $cat->title,
            "catdesc" => $cat->detail->descript
        );

        for ($i = 0; $i <= 2; $i++) {
            if ($i == 0) {
                $elType = $elTypeList->Get(0);
            } else if ($el->elTypeId > 0) {
                $elType = $elTypeList->Get($el->elTypeId);
            } else {
                continue;
            }

            for ($ii = 0; $ii < $elType->options->Count(); $ii++) {
                $option = $elType->options->GetByIndex($ii);

                $value = $this->GetOptionValue($option->name);

                if ($option->type == Catalog::TP_TABLE) {
                    $tblval = $option->values[$value];
                    if (!empty($tblval)) {
                        $value = $tblval['tl'];
                    }
                } else {
                    if ($option->name == 'price') {
                        $value = number_format($value, 2, ',', ' ');
                    }
                }
                $replace["fldtl_".$option->name] = $option->title;
                $replace["fld_".$option->name] = $value;
            }
        }

        return $replace;
    }

}

?>