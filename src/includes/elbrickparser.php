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

    private $_cacheOptionsData = null;

    public function GetOptionsData() {
        if (!empty($this->_cacheOptionsData)) {
            return $this->_cacheOptionsData;
        }

        $el = $this->element;

        $cMan = EShopModule::$instance->GetManager()->cManager;
        $elTypeList = $cMan->ElementTypeList();

        $ret = array();
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

                $reti = array(
                    "option" => $option,
                    "title" => $option->title,
                    "value" => $value,
                    "value_int" => ""
                );

                if ($option->type == Catalog::TP_TABLE) {
                    $tblval = isset($option->values[$value]) ? $option->values[$value] : "";
                    if (!empty($tblval)) {
                        $reti['value'] = $tblval['tl'];
                    }
                } else if ($option->type == Catalog::TP_DOUBLE || $option->type == Catalog::TP_CURRENCY) {
                    $reti['value_int'] = number_format($value, 0, ',', ' ');

                    $reti['value'] = number_format($value, 2, ',', ' ');

                }
                $ret[$option->name] = $reti;
            }
        }

        return $this->_cacheOptionsData = $ret;
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
        $v = &$brick->param->var;
        $el = $this->element;
        $replace = array();

        $cMan = EShopModule::$instance->GetManager()->cManager;

        $optionGroupList = $cMan->ElementOptionGroupList();
        $elTypeList = $cMan->ElementTypeList();

        $v['optiongroups'] = isset($v['optiongroups']) ? $v['optiongroups'] : '';
        $aOptionGroups = explode(",", $v['optiongroups']);

        foreach ($aOptionGroups as $sOptGroup) {
            $sOptGroup = trim($sOptGroup);
            $tpOptGroup = isset($v['optiongroup-'.$sOptGroup]) ? $v['optiongroup-'.$sOptGroup] : '';
            $tpOptGRow = isset($v['optiongrouprow-'.$sOptGroup]) ? $v['optiongrouprow-'.$sOptGroup] : '';
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

            $replace['optiongroup-'.$sOptGroup] = Brick::ReplaceVarByData($tpOptGroup, array("rows" => $lst));
        }
        return $replace;
    }

    public function ParseOptions(Ab_CoreBrick $brick) {
        $v = &$brick->param->var;
        $replace = array();

        $optionsData = $this->GetOptionsData();

        $v['options'] = isset($v['options']) ? $v['options'] : '';

        $aOptions = explode(",", $v['options']);
        foreach ($aOptions as $sOption) {
            $sOption = trim($sOption);
            if (empty($sOption)) {
                continue;
            }

            $replace['option-'.$sOption] = "";
            $value = $this->GetOptionValue($sOption);

            if (empty($value) || empty($v['option-'.$sOption]) || $value == '0.00' // временно (для отключения опций с плавающей точкой)
            ) {
                continue;
            }

            $optData = $optionsData[$sOption];

            $replace['option-'.$sOption] = Brick::ReplaceVarByData($v['option-'.$sOption], array(
                "fldvalue" => $optData['value'],
                "fldtitle" => $optData['title']
            ));
        }

        return $replace;
    }

    public function GetReplaceData() {

        $el = $this->element;

        $cMan = EShopModule::$instance->GetManager()->cManager;

        $catList = $cMan->CatalogList();

        $replace = array(
            "link" => $el->URI(),
            "elementid" => $el->id,
            "title" => $el->title,
            "name" => $el->name,
            "currency" => $cMan->CurrencyDefault()->postfix
        );

        $cat = $catList->Get($el->catid);
        if (!empty($cat) && $cat->id > 0) {
            $cat["cattitle"] = $cat->title;
            $cat["catdesc"] = $cat->detail->descript;
        }

        $optionsData = $this->GetOptionsData();

        foreach ($optionsData as $optName => $optData) {
            $option = $optData['option'];
            $replace["fldtl_".$optName] = $option->title;
            $replace["fld_".$optName] = $optData['value'];
            if (!empty($optData['value_int'])) {
                $replace["fld_".$optName."_int"] = $optData['value_int'];
            }
        }

        return $replace;
    }

}

?>