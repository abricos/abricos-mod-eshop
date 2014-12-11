<?php

/**
 * @package Abricos
 * @subpackage EShop
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @author Alexander Kuzmin <roosit@abricos.org>
 */
class EShopElementBrickBuilder {

    /**
     * @var CatalogElement
     */
    public $element;

    /**
     * @var Ab_CoreBrick
     */
    public $brick;

    /**
     * @var Catalog
     */
    public $catalog;

    /**
     * @var EShopElementBrickParser
     */
    public $parser;

    public function __construct(CatalogElement $el, Ab_CoreBrick $brick, $isTypeBrick = false) {
        $this->element = $el;
        $this->brick = $brick;

        $man = EShopModule::$instance->GetManager()->cManager;
        $this->catalog = $man->Catalog($el->catid);

        $this->parser = new EShopElementBrickParser($el);

        if (!$isTypeBrick) {
            $this->OverrideByElementType();
        }
    }

    private function OverrideByElementType() {
        $brick = $this->brick;

        $man = EShopModule::$instance->GetManager()->cManager;

        $elType = $man->ElementTypeList()->Get($this->element->elTypeId);

        $elTypeBrick = Brick::$builder->LoadBrickS("eshop", $brick->name."-tp-".$elType->name);

        if (!empty($elTypeBrick) && !$elTypeBrick->isError) {
            $p = &$brick->param->param;
            $pOvr = &$elTypeBrick->param->param;
            foreach ($p as $name => $value) {
                if (isset($pOvr[$name])) {
                    $p[$name] = $pOvr[$name];
                }
            }

            $v = &$brick->param->var;
            $vOvr = &$elTypeBrick->param->var;
            foreach ($v as $name => $value) {
                if (isset($vOvr[$name])) {
                    $v[$name] = $vOvr[$name];
                }
            }

            $ph = &$brick->param->phrase;
            $phOvr = &$elTypeBrick->param->phrase;

            foreach ($phOvr as $name => $value) {
                $ph[$name] = $phOvr[$name];
            }

            $contentOvr = trim($elTypeBrick->content);
            if (!empty($contentOvr)) {
                $brick->content = $contentOvr;
            }
        }
    }

    private function LoadIncludeBricks() {
        $el = $this->element;
        $cat = $this->catalog;

        $brick = $this->brick;
        $v = &$brick->param->var;

        $sIncBricks = isset($v['includebrick']) ? $v['includebrick'] : "";
        $aIncBricks = explode(",", $sIncBricks);

        $replace = array();

        foreach ($aIncBricks as $sIncBrick) {
            $sIncBrick = trim($sIncBrick);
            if (empty($sIncBrick)) {
                continue;
            }

            $incBrickParams = array(
                "element" => $el,
                "cat" => $cat
            );

            // если есть параметры
            $aIncBrickParam = explode("|", $sIncBrick);
            if (count($aIncBrickParam) > 1) {
                $sIncBrick = $aIncBrickParam[0];
                for ($i = 1; $i < count($aIncBrickParam); $i++) {
                    $sPrm = $aIncBrickParam[$i];
                    $aPrm = explode("=", $sPrm);
                    if (count($aPrm) == 2) {
                        $incBrickParams[$aPrm[0]] = $aPrm[1];
                    }
                }
            }

            $incBrick = Brick::$builder->LoadBrickS("eshop", $sIncBrick, null, array("p" => $incBrickParams));
            if (!empty($incBrick) && !$incBrick->isError) {
                $this->parser->Parse($incBrick);

                $replace["brick_".$sIncBrick] = $incBrick->content;
            }
        }

        $brick->content = Brick::ReplaceVarByData($brick->content, $replace);
    }

    public function Build() {
        $this->LoadIncludeBricks();

        $brick = $this->brick;

        $this->parser->Parse($brick);

        $replace = $this->parser->GetReplaceData();
        $replace['brickid'] = $brick->id;
        $brick->content = Brick::ReplaceVarByData($brick->content, $replace);
    }

}

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

        $aOptions = json_decode($v['options']);

        if (is_object($aOptions)){
            $aOptions = array($aOptions);
        }

        if (!is_array($aOptions)){
            return $replace;
        }

        foreach ($aOptions as $oOption) {
            if (is_string($oOption)){
                $obj = new stdClass();
                $obj->name = $oOption;
                $oOption = $obj;
            }
            if (!is_object($oOption)) {
                continue;
            }

            $sOption = isset($oOption->name) ? $oOption->name : "";
            if (empty($sOption)) {
                continue;
            }

            $tplCount = isset($oOption->count) ? intval($oOption->count) : 1;

            for ($i = 1; $i <= $tplCount; $i++) {

                $tplPostfix = $tplCount === 1 ? "" : "-".$i;

                $replace['option-'.$sOption.$tplPostfix] = "";
                $value = $this->GetOptionValue($sOption);

                if (empty($value) || $value == '0.00' // временно (для отключения опций с плавающей точкой)
                ) {
                    $tplOptionName = 'option-'.$sOption.'-empty'.$tplPostfix;
                } else {
                    $tplOptionName = 'option-'.$sOption.$tplPostfix;
                }

                if (!isset($v[$tplOptionName])) {
                    continue;
                }

                $optData = $optionsData[$sOption];

                $tplOption = Brick::ReplaceVarByData($v[$tplOptionName], array(
                    // TODO: remove
                    "fldvalue" => $optData['value'],
                    "fldtitle" => $optData['title']
                ));;

                $tplContainer = "option-".$sOption."-container".$tplPostfix;

                if (isset($v[$tplContainer])) {
                    $replace['option-'.$sOption.$tplPostfix] = Brick::ReplaceVarByData($v[$tplContainer], array(
                        "result" => $tplOption
                    ));
                } else {
                    $replace['option-'.$sOption.$tplPostfix] = $tplOption;
                }
            }
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