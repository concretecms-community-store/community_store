<?php
namespace Concrete\Package\CommunityStore\Src\CommunityStore\Payment\Methods\Invoice;

use Core;
use Config;
use Concrete\Package\CommunityStore\Src\CommunityStore\Payment\Method as StorePaymentMethod;

class InvoicePaymentMethod extends StorePaymentMethod
{
    public function getName()
    {
      return t('Invoice');
    }

    public function dashboardForm()
    {
        $this->set('form', Core::make("helper/form"));
        $this->set('invoiceMinimum', Config::get('community_store.invoiceMinimum'));
        $this->set('invoiceMaximum', Config::get('community_store.invoiceMaximum'));
    }

    public function save(array $data = [])
    {
        Config::save('community_store.invoiceMinimum', $data['invoiceMinimum']);
        Config::save('community_store.invoiceMaximum', $data['invoiceMaximum']);
    }
    public function validate($args, $e)
    {

        //$e->add("error message");
        return $e;
    }
    public function checkoutForm()
    {
        $pmID = StorePaymentMethod::getByHandle('invoice')->getID();
        
        $this->addFooterItem("
            <script type=\"text/javascript\">
                 $(function() {
                     $('div[data-payment-method-id=".$pmID."] .store-btn-complete-order').click(function(){
                         $(this).attr({disabled: true}).val('" .  t('Processing...').  "');
                         $(this).closest('form').submit();
                     });
                 });
            </script>
        ");
    }

    public function submitPayment()
    {

        //nothing to do except return success
        return array('error' => 0, 'transactionReference' => '');
    }

    public function getPaymentMinimum()
    {
        $defaultMin = 0;

        $minconfig = trim(Config::get('community_store.invoiceMinimum'));

        if ($minconfig == '') {
            return $defaultMin;
        } else {
            return max($minconfig, $defaultMin);
        }
    }

    public function getPaymentMaximum()
    {
        $defaultMax = 1000000000;

        $maxconfig = trim(Config::get('community_store.invoiceMaximum'));
        if ($maxconfig == '') {
            return $defaultMax;
        } else {
            return min($maxconfig, $defaultMax);
        }
    }
}
