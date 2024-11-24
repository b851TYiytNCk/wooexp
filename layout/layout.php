<?php
/**
 * Function that returns HTML layout for WooCommerce Export plugin
 * to be output at WooCommerce Order Edit Screen
 * @return
 */
function get_order_export_layout() {
    ?>
    <style id="wooexp-print-style">
        html:has(.wooexp-body-covered) {
            padding: 0;
            margin: 0;
        }

        #wooexp {
            display: none;
        }

        .wp-core-ui .button.wooexp-btn {
            margin: 20px;
        }

        .wooexp-body-covered {
            --wooBlue: #0083ff;
            --wooBg: #fff;
            position: relative;
            z-index: 999;
            background: var(--wooBg);
        }

        .wooexp-body-covered:before {
            content: '';
            position: fixed;
            width: 100%;
            height: 100%;
            background: var(--wooBg);
            z-index: 999;
        }

        .wooexp-body-covered:after {
            content: '';
            position: fixed;
            inset: 50%;
            z-index: 999;
            width: 50px;
            height: 50px;
            border: 6px solid transparent;
            border-top: 6px solid var(--wooBlue);
            border-bottom: 6px solid var(--wooBlue);
            border-radius: 100%;
            animation: 1.5s linear infinite spinCircle;
        }

        @keyframes spinCircle {
            0% {
                transform: rotate(0deg);
            }
            100% {
                transform: rotate(360deg);
            }
        }

        .wooexp-body-covered:after {
            background: #fff;
        }

        @media print {
            .wooexp-body-covered:after,
            .wooexp-body-covered:before {
                display: none;
            }

            .wooexp-body-covered table {
                border-collapse: collapse;
                margin: auto;
            }

            .wooexp-body-covered td {
                width: auto;
                padding: 20px;
            }

            .wooexp-body-covered tr {
                border: 1px solid #000;
                border-bottom: 0;
            }

            .wooexp-body-covered .wooexp-num-row {
                border: 1px solid #000;
                border-top: 0;
            }

            .wooexp-body-covered th {
                padding: 20px;
            }
        }
    </style>
    <script id="wooexp-script">
    ( () => {
        var $ = jQuery;

        if ( ! $ ) {
            return console.error('jQuery is absent. Aborting');
        }

        $(() => {
            /**
             * WooCommerce Order item list
             * @type {jQuery|HTMLElement|*}
             */
            var wooItemList = $('#woocommerce-order-items');

            /**
             * Button that triggers printing
             */
            var btn = document.createElement('div');
            btn.innerText = 'Export Guide';
            btn.classList.add('button', 'button-primary', 'wooexp-btn');

            if (wooItemList.length) {
                wooItemList.prepend(btn);
            }

            btn.addEventListener('click', () => {
                printElement(wooItemList);
            })

            /**
             * Print only specific element of a page
             *
             * @param id
             */
            function printElement($itemList) {
                var targetEl = $itemList.clone();
                var origBodyHTML = document.body.innerHTML;

                if ( !targetEl.length ) {
                    return console.error('There is no such element on the page.');
                }

                targetEl.find('.item').each(function() {
                    const $this = $(this);
                    $this.find('.thumb').appendTo($this);

                    /**
                     * Create a row that contains item cost and quantity
                     */
                    const tr = $('<tr>');
                    tr.addClass('wooexp-num-row');

                    tr.insertAfter($this);

                    $this.find('td.item_cost').appendTo(tr);
                    $this.find('td.quantity').appendTo(tr);

                    const lineCost = $this.find('td.line_cost');
                    lineCost.find('.refunded, .wc-order-item-discount').remove();
                    lineCost.appendTo(tr);
                })

                /**
                 * Clear UI to leave product data only
                 */
                targetEl
                    .find('.postbox-header, thead, #order_shipping_line_items, .button, p')
                    .remove();

                targetEl.find('.wc-order-totals-items, #order_fee_line_items, .wc-order-bulk-actions, #order_refunds')
                    .remove();

                targetEl.prepend($('#wooexp-print-style'));

                /**
                 * Return original HTML contents after successful printing
                 */
                $(window).on('afterprint', () => {
                    document.body.innerHTML = origBodyHTML;
                    document.body.classList.remove('wooexp-body-covered');
                    location.reload();
                })

                /**
                 * Print order items list only
                 */
                document.body.classList.add('wooexp-body-covered');
                document.body.innerHTML = targetEl.html();
                window.print();
            }
        });

    } )();
    </script>
    <?php
}