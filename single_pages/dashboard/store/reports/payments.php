<?php

declare(strict_types=1);

/**
 * @var Concrete\Package\CommunityStore\Controller\SinglePage\Dashboard\Store\Reports\Payments $controller
 * @var Concrete\Core\Form\Service\Form $form
 * @var Concrete\Core\Html\Service\Html $html
 * @var Concrete\Core\Application\Service\UserInterface $interface
 * @var Concrete\Core\Validation\CSRF\Token $token
 * @var Concrete\Core\Page\View\PageView $view
 * @var Concrete\Core\Url\Resolver\Manager\ResolverManagerInterface $urlResolver
 * @var Concrete\Core\Localization\Localization $localization
 * @var string $date
 * @var int|null $orderID
 */

defined('C5_EXECUTE') or die('Access Denied.');

?>
<div id="cs-app" v-cloak>
    <form v-on:submit.prevent="fetch">
        <div class="row">
            <div class="col-sm-4 col-xl-2">
                <?= $form->label('cs-listBy', t('List by')) ?>
                <?= $form->select(
                    'cs-listBy',
                    [
                        'date' => t('Date'),
                        'orderID' => t('Order ID'),
                    ],
                    '',
                    [
                        'v-bind:disabled' => 'busy',
                        'v-model' => 'listBy',
                    ]
                ) ?>
            </div>
            <div class="col-sm-4 col-xl-2">
                <div class="form-group" v-if="listBy === 'date'">
                    <?= $form->label('cs-date', t('Date')) ?>
                    <div class="input-group">
                        <span class="input-group-btn">
                            <button class="btn btn-default btn-secondary px-1" type="button" v-bind:disabled="busy" v-on:click.prevent="addDate(-1, true)">&larr;</button>
                        </span>
                        <?= $form->date(
                            'cs-date',
                            '',
                            [
                                'v-bind:max' => 'today',
                                'v-model' => 'date',
                                'v-bind:readonly' => 'busy',
                            ]
                        ) ?>
                        <span class="input-group-btn">
                            <button class="btn btn-default btn-secondary px-1" type="button" v-bind:disabled="busy || date === today" v-on:click.prevent="addDate(1, true)">&rarr;</button>
                        </span>
                    </div>
                </div>
                <div class="form-group" v-else-if="listBy === 'orderID'">
                    <?= $form->label('cs-order', t('Order ID')) ?>
                    <?= $form->number(
                        'cs-order',
                        '',
                        [
                            'v-model' => 'orderID',
                            'v-bind:readonly' => 'busy',
                            'min' => '1',
                            'max' => (string) PHP_INT_MAX,
                        ]
                    ) ?>
                </div>
            </div>
            <div class="col-sm-2  col-md-1 text-right text-end">
                <div class="form-group">
                    <?= $form->label('', '&nbsp;') ?><br />
                    <button class="btn btn-primary" v-on:click.prevent="fetch" v-bind:disabled="busy">
                        <?= t('Search') ?>
                    </button>
                </div>
            </div>
        </div>
    </form>
    <div v-if="loaded">
        <div v-if="entries.length === 0" class="alert alert-info">
            <?= t('No entries found') ?>
        </div>
        <table v-else class="table table-striped table-hover">
            <colgroup>
                <col width="1" />
                <col width="1" />
            </colgroup>
            <thead>
                <tr>
                    <th class="text-nowrap"><?= t('Order') ?></th>
                    <th class="text-nowrap"><?= t('Date') ?></th>
                    <th><?= t('Method') ?></th>
                    <th><?= t('Type') ?></th>
                    <th><?= t('Data') ?></th>
                    <th><?= t('Error') ?></th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="entry in entries">
                    <td><a v-if="entry.orderUrl !== ''" target="_blank" v-bind:href="entry.orderUrl">{{ entry.orderID }}</a></td>
                    <td class="text-nowrap">{{ entry.dateString }}</td>
                    <td>{{ entry.paymentMethod }}</td>
                    <td>{{ entry.type }}</td>
                    <td>
                        <button v-if="entry.data" class="btn btn-secondary btn-sm" v-on:click.prevent="viewEntryData(entry)"><?= t('View') ?></button>
                    </td>
                    <td>
                        <div v-if="entry.error" class="text-danger" style="white-space: pre-wrap">{{ entry.error }}</div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
    <div class="d-none hide">
        <div ref="cs-payment-data" title="<?= h(t('Data sent/received'))?>" class="ccm-ui">
            <div>
                <div v-if="entryForDataViewer !== null">
                    <div v-if="typeof entryForDataViewer.data === 'string'" style="white-space: pre-wrap">{{ entryForDataViewer.data }}</div>
                    <table v-else-if="entryForDataViewer.data instanceof Array" class="table table-striped table-hover table-sm table-condensed mb-0">
                        <tbody>
                            <tr v-for="row in entryForDataViewer.data">
                                <th v-if="typeof row === 'string'" colspan="2" class="text-center">{{ row }}</th>
                                <th v-if="typeof row !== 'string' && row[0]">{{ row[0] }}</th>
                                <td v-if="typeof row !== 'string'" style="white-space: pre-wrap" v-bind:colspan="row[0] ? 1 : 2">{{ row[1] }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
$(document).ready(() => {

const DATETIME_FORMAT = new Intl.DateTimeFormat(
    <?= json_encode(str_replace('_', '-', $localization->getLocale())) ?>,
    {
        dateStyle: 'short',
        timeStyle: 'medium',
    }
);

new Vue({
    el: '#cs-app',
    data() {
        const today = new Date().toISOString().split('T')[0];
        return {
            busy: false,
            today,
            lastKnownGoodDate: today,
            listBy: <?= json_encode($orderID === null ? 'date' : 'orderID') ?>,
            date: <?= json_encode($date) ?> || today,
            orderID: <?= json_encode($orderID) ?>,
            entries: [],
            loaded: false,
            entryForDataViewer: null,
        };
    },
    watch: {
        date() {
            if (this.buildDateObject() === null) {
                this.date = this.lastKnownGoodDate;
            } else {
                this.lastKnownGoodDate = this.date;
            }
        },
    },
    mounted() {
        this.fetch();
    },
    methods: {
        buildDateObject() {
            if (!this.date || !/^\d{4}-\d{2}-\d{2}$/.test(this.date) || this.date > this.today) {
                return null;
            }
            const date = new Date(this.date);
            return date && date.toString() !== 'Invalid Date' ? date : null;
        },
        addDate(deltaDays, doFetch) {
            const date = this.buildDateObject() || new Date();
            date.setHours(12);
            date.setTime(date.getTime() + deltaDays * 24 * 60 * 60 * 1000);
            let newDate = date.toISOString().split('T')[0];
            if (newDate > this.today) {
                newDate = this.today;
            }
            if (this.date === newDate) {
                return;
            }
            this.date = newDate;
            if (doFetch) {
                this.fetch();
            }
        },
        async fetch() {
            if (this.busy) {
                return;
            }
            this.busy = true;
            this.updateUrl();
            try {
                const requestBody = new URLSearchParams();
                requestBody.append(<?= json_encode($token::DEFAULT_TOKEN_NAME) ?>, <?= json_encode($token->generate('cs-pm-log')) ?>);
                switch (this.listBy) {
                    case 'date':
                        if (!this.buildDateObject()) {
                            throw new Error(<?= json_encode(t('Please specify the date')) ?>);
                        }
                        requestBody.append('date', this.date);
                        break;
                    case 'orderID':
                        if (!this.orderID) {
                            throw new Error(<?= json_encode(t('Please specify the order ID')) ?>);
                        }
                        requestBody.append('orderID', this.orderID);
                        break;
                    default:
                        throw new Error(<?= json_encode(t('Please specify the search criteria')) ?>);
                }
                requestBody.append('listBy', this.listBy);
                const response = await fetch(
                    <?= json_encode((string) $controller->action('fetch')) ?>,
                    {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        body: requestBody,
                    }
                );
                const data = await response.json();
                if (data.error) {
                    throw new Error(data.error.message || data.error);
                } else {
                }
                this.loaded = true;
                this.entries.splice(0, this.entries.length);
                data.forEach((entry) => this.entries.push(this.patchEntry(entry)));
            } catch (e) {
                window.alert(e.message || e);
            }
            this.busy = false;
        },
        patchEntry(entry) {
            entry.date = new Date();
            entry.date.setTime(entry.timestamp * 1000);
            entry.dateString = DATETIME_FORMAT.format(entry.date);
            delete entry.timestamp;
            if (entry.orderID) {
                entry.orderUrl = <?= json_encode((string) $urlResolver->resolve(['/dashboard/store/orders/order/', PHP_INT_MAX])) ?>.replace('<?= PHP_INT_MAX ?>', entry.orderID);
            } else {
                entry.orderUrl = '';
            }
            return entry;
        },
        viewEntryData(entry) {
            this.entryForDataViewer = entry;
            const $dialog = $(this.$refs['cs-payment-data']);
            $dialog.find('>div')
                .css({
                    'max-height': Math.min(Math.max($(window).height() - 170, 200), 1500) + 'px',
                    'overflow-y': 'auto',
                })
            ;
            this.$nextTick(() => {
                $dialog.dialog({
                    modal: true,
                    width: Math.min(Math.max($(window).width() - 20, 200), 1500),
                });
            });
        },
        updateUrl() {
            let url = window.location.href.replace(/[#?].*$/, '');
            switch (this.listBy) {
                case 'date':
                    if (this.buildDateObject() !== null) {
                        url += '?date=' + this.date;
                    }
                    break;
                case 'orderID':
                    if (this.orderID) {
                        url += '?orderID=' + this.orderID;
                    }
                    break;
            }
            window.history.replaceState(null, null, url);
        },
    },
});

});
</script>
