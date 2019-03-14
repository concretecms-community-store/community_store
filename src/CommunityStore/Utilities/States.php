<?php
namespace Concrete\Package\CommunityStore\Src\CommunityStore\Utilities;

use Controller;
use Core;

class States extends Controller
{
    public function getStateList()
    {
        $service = \Core::make('helper/security');
        $countryCode = $service->sanitizeString($_POST['country']);
        $selectedState = $service->sanitizeString($_POST['selectedState']);
        $type = $service->sanitizeString($_POST['type']);
        $class = empty($_POST['class']) ? 'form-control' : $service->sanitizeString($_POST['class']);
        $dataList = Core::make('helper/json')->decode($_POST['data'], true);
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

        $list = Core::make('helper/lists/states_provinces')->getStateProvinceArray($countryCode);
        if ($list) {
            if ("tax" == $type) {
                echo "<select name='taxState' id='taxState' class='{$class}'{$data}>";
            } else {
                echo "<select $required name='store-checkout-{$type}-state' id='store-checkout-{$type}-state' ccm-passed-value='' class='{$class}'{$data}>";
            }
            echo '<option value=""></option>';

            foreach ($list as $code => $country) {
                if ($code == $selectedState) {
                    echo "<option selected value='{$code}'>{$country}</option>";
                } else {
                    echo "<option value='{$code}'>{$country}</option>";
                }
            }
            echo "<select>";
        } else {
            if ("tax" == $type) {
                echo "<input type='text' name='taxState' id='taxState' class='{$class}'{$data}>";
            } else {
                echo "<input type='text' name='store-checkout-{$type}-state' id='store-checkout-{$type}-state' value='{$selectedState}' class='{$class}'{$data} placeholder='" . t('State / Province') . "'>";
            }
        }
    }
}
