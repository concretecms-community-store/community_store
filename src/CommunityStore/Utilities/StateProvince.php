<?php
namespace Concrete\Package\CommunityStore\Src\CommunityStore\Utilities;

use Concrete\Core\Controller\Controller;

class StateProvince extends Controller
{
    public function getStates()
    {
        $request = $this->request->request;
        $countryCode = (string) $request->get('country');
        $selectedState = (string) $request->get('selectedState');
        $type = (string) $request->get('type');
        if (!in_array($type, ['billing', 'shipping', 'tax'], true)) {
            $type = 'billing';
        }
        $class = trim((string) $request->get('class'));
        if ($class === '') {
            $class = 'form-control';
        }
        // only allow simple class names
        $class = preg_replace('/[^\w\s-]/', '', $class);

        $dataList = $this->app->make('helper/json')->decode((string) $request->get('data'), true);
        $data = '';
        if (is_array($dataList)) {
            foreach ($dataList as $name => $value) {
                // only allow valid data attribute names and scalar values
                if (preg_match('/^[a-zA-Z][\w-]*$/', (string) $name) && is_scalar($value)) {
                    $data .= ' data-' . h($name) . '="' . h($value) . '"';
                }
            }
        }

        $requiresstate = ['US', 'AU', 'CA', 'CN', 'MX', 'MY'];

        $required = '';

        if (in_array($countryCode, $requiresstate)) {
            $required = ' required="required"';
        }

        if ('tax' == $type) {
            $name = 'taxState';
        } else {
            $name = 'store-checkout-' . $type . '-state';
        }

        $ret = '';
        $list = $countryCode === '' ? [] : $this->app->make('helper/lists/states_provinces')->getStateProvinceArray($countryCode);
        if ($list) {
            $class = trim(str_replace('form-select', '', $class)) . ' form-select';

            $ret .= '<select' . ('tax' == $type ? '' : $required) . ' name="' . h($name) . '" id="' . h($name) . '" class="' . h(trim($class)) . '"' . $data . '>';

            $hasSelectedState = $selectedState !== '' && isset($list[$selectedState]);
            $ret .= '<option ' . ($hasSelectedState ? '' : 'selected ') . 'value=""></option>';

            foreach ($list as $code => $state) {
                $selected = $hasSelectedState && (string) $code === $selectedState ? 'selected ' : '';
                $ret .= '<option ' . $selected . 'value="' . h($code) . '">' . h($state) . '</option>';
            }

            $ret .= '</select>';
        } else {
            $class = trim(str_replace('form-select', '', $class));

            if (!$class) {
                $class = 'form-control';
            }

            $placeholder = 'tax' == $type ? '' : ' placeholder="' . h(t('State / Province')) . '"';
            $ret .= '<input type="text" name="' . h($name) . '" id="' . h($name) . '" value="' . h($selectedState) . '" class="' . h($class) . '"' . $data . $placeholder . '>';
        }

        echo $ret;

        exit();
    }
}
