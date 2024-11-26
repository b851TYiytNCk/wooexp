<?php
/**
 * Function that returns HTML layout for WooCommerce Export plugin
 * to be output at WooCommerce Order Edit Screen
 */

function get_order_export_layout() {
    ?>
    <style id="wooexp-style">
        html:has(.wooexp-body-covered) {
            padding: 0;
            margin: 0;
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

        .wooexp-customer,
        .wooexp-body-covered .woocommerce-order-data__heading {
            display: none;
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

            .wooexp-body-covered .thumb img {
                width: 300px;
                height: auto;
            }

            .wooexp-body-covered .thumb .small-width {
                width: 150px;
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

            .wooexp-customer,
            .wooexp-body-covered .woocommerce-order-data__heading {
                display: block;
                margin-bottom: 0.8em;
            }

            .wooexp-body-covered .cfield_oitem_reject,
            .wooexp-body-covered .wrap_note_item {
                margin: 0.5em;
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

                const orderItems = targetEl.find('#order_line_items .item');
                orderItems.each( function(i) {
                    const $this  = $(this);

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

                    /**
                     * Replace thumbnail with artwork original image and place to the right side
                     */
                    const imgThumb = $this.find('.thumb')
                        .appendTo($this)
                        .find('img');

                    const imgThumbSrc = imgThumb.attr('src');
                    var backToSrc;

                    imgThumb
                        .on('load', function() {
                            $(this).removeAttr('width height');

                            if (backToSrc) {
                                imgThumb.addClass('small-width');
                            }

                            if (i === orderItems.length - 1) {
                                setPrinting(targetEl, origBodyHTML);
                            }
                        })
                        .on('error', function() {
                            if (imgThumb.attr('src') !== imgThumbSrc) {
                                imgThumb.attr('src', imgThumbSrc);
                                backToSrc = true;
                            } else if (backToSrc) {
                                imgThumb.addClass('small-width');

                                if (i === orderItems.length - 1) {
                                    setPrinting(targetEl, origBodyHTML);
                                }
                            }
                        })
                        .attr('src',
                            $this.find('.download-artwork')
                                .attr('href')
                        )
                })
            }

            function setPrinting(targetEl, origBodyHTML) {
                /**
                 * Clear layout to leave only product data in product section
                 */
                targetEl
                    .find('.postbox-header, thead, .button, p:not(.wrap_note_item, .cfield_oitem_reject), .wc-order-bulk-actions')
                    .remove();

                targetEl.find('.wc-order-totals-items, #order_shipping_line_items, #order_fee_line_items, #order_refunds')
                    .remove();

                /**
                 * Add customer details to export layout
                 */
                const customer = $('.wc-customer-search').find(':selected');
                if (customer.length) {
                    const customerHtml = $(`<h3></h3>`);
                    customerHtml.addClass('wooexp-customer');
                    customerHtml.html(`Customer details: ${customer.text()}`);
                    targetEl.prepend(customerHtml);
                }

                /**
                 * Prepend order number
                 */
                targetEl.prepend($('.woocommerce-order-data__heading'));

                /**
                 * Attach styles to cloned element
                 */
                targetEl.prepend($('#wooexp-style'));

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