<?php
namespace Concrete\Package\CommunityStore\Src\CommunityStore\Utilities;

use Concrete\Core\Controller\Controller;

class StateProvince extends Controller
{
    public function getStates()
    {
        $service = $this->app->make('helper/security');
        $countryCode = $service->sanitizeString($this->request->request->get('country'));
        $selectedState = $service->sanitizeString($this->request->request->get('selectedState'));
        $type = $service->sanitizeString($this->request->request->get('type'));
        $class = empty($this->request->request->get('class')) ? 'form-control' : $service->sanitizeString($this->request->request->get('class'));
        $dataList = $this->app->make('helper/json')->decode($this->request->request->get('data'), true);
        $data = '';
        if (is_array($dataList) && count($dataList)) {
            foreach ($dataList as $name => $value) {
                $data .= ' data-' . $name . '="' . $value . '"';
            }
        }

        $requiresstate = ['US', 'AU', 'CA', 'CN', 'MX', 'MY'];

        $required = '';

        if (in_array($countryCode, $requiresstate)) {
            $required = ' required="required" ';
        }

        $ret = '';
        $list = $this->app->make('helper/lists/states_provinces')->getStateProvinceArray($countryCode);
        if ($list) {
            $class = trim(str_replace('form-select', '', $class));
            $class .= ' form-select';

            if ("tax" == $type) {
                $ret .= "<select name='taxState' id='taxState' class='{$class}'{$data}>";
            } else {
                $ret .= "<select $required name='store-checkout-{$type}-state' id='store-checkout-{$type}-state' ccm-passed-value='' class='{$class}'{$data}>";
            }

            $ret .= '<option {selected} value=""></option>';
            $hasSelectedState = false;

            foreach ($list as $code => $country) {
                if (!empty($selectedState) && $code == $selectedState) {
                    $ret .= "<option selected value='{$code}'>{$country}</option>";
                    $hasSelectedState = true;
                } else {
                    $ret .= "<option value='{$code}'>{$country}</option>";
                }
            }

            $ret .= "</select>";

            if ($hasSelectedState) {
                $ret = str_replace('{selected}', '', $ret);
            } else {
                $ret = str_replace('{selected}', 'selected', $ret);
            }
        } else {
            $class = trim(str_replace('form-select', '', $class));

            if (!$class) {
                $class = 'form-control';
            }

            if ("tax" == $type) {
                $ret .= "<input type='text' name='taxState' id='taxState' class='{$class}'{$data}>";
            } else {
                $ret .= "<input type='text' name='store-checkout-{$type}-state' id='store-checkout-{$type}-state' value='{$selectedState}' class='{$class}'{$data} placeholder='" . t('State / Province') . "'>";
            }
        }

        echo $ret;

        exit();
    }
}
