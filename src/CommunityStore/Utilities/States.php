<?php
namespace Concrete\Package\CommunityStore\Src\CommunityStore\Utilities;

use Controller;
use Core;

defined('C5_EXECUTE') or die(_("Access Denied."));

class States extends Controller
{
    public function getStateList()
    {
        $countryCode = $_POST['country'];
        $selectedState = $_POST['selectedState'];
        $type = $_POST['type'];
        $list = Core::make('helper/lists/states_provinces')->getStateProvinceArray($countryCode);
        if ($list) {
            if ($type == "tax") {
                echo "<select name='taxState' id='taxState' class='form-control'>";
            } else {
                echo "<select required='required' name='store-checkout-{$type}-state' id='store-checkout-{$type}-state' ccm-passed-value='' class='form-control'>";
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
                echo "<input type='text' name='taxState' id='taxState' class='form-control'>";
            } else {
                echo "<input type='text' name='store-checkout-{$type}-state' id='store-checkout-{$type}-state' value='{$selectedState}' class='form-control' placeholder='".t('State / Province')."'>";
            }
        }
    }
}
