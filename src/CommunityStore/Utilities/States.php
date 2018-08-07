<?php
namespace Concrete\Package\CommunityStore\Src\CommunityStore\Utilities;

use Controller;
use Core;

class States extends Controller
{
    public function getStateList()
    {
        $countryCode = $_POST['country'];
        $selectedState = $_POST['selectedState'];
        $type = $_POST['type'];
        $class = empty($_POST['class']) ? 'form-control' : $_POST['class'];
        $dataList = Core::make('helper/json')->decode($_POST['data'], true);
        $data = '';
        if (is_array($dataList) && count($dataList)) {
            foreach ($dataList as $name => $value) {
                $data .= ' data-' . $name . '="' . $value . '"';
            }
        }
        $list = Core::make('helper/lists/states_provinces')->getStateProvinceArray($countryCode);
        if ($list) {
            if ($type == "tax") {
                echo "<select name='taxState' id='taxState' class='{$class}'{$data}>";
            } else {
                echo "<select required='required' name='store-checkout-{$type}-state' id='store-checkout-{$type}-state' ccm-passed-value='' class='{$class}'{$data}>";
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
            if ($type == "tax") {
                echo "<input type='text' name='taxState' id='taxState' class='{$class}'{$data}>";
            } else {
                echo "<input type='text' name='store-checkout-{$type}-state' id='store-checkout-{$type}-state' value='{$selectedState}' class='{$class}'{$data} placeholder='".t('State / Province')."'>";
            }
        }
    }
}