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

        @media screen {
            .wooexp-customer,
            .wooexp-body-covered .woocommerce-order-data__heading,
            .wooexp-body-covered [data-name="order_notes_admin"],
            #woo-item-list {
                display: none;
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
                    transform: translate(-50%, -50%) rotate(0deg);
                }
                100% {
                    transform: translate(-50%, -50%) rotate(360deg);
                }
            }
        }

        @media print {
            html {
                height: 0;
            }

            .wooexp-body-covered:after,
            .wooexp-body-covered:before,
            [data-name="order_notes_admin"] .acf-input,
            .item-wrap table.display_meta {
                display: none;
            }

            .wooexp-body-covered table {
                width: 100%;
                border-collapse: collapse;
                margin: auto;
                break-after: avoid;
            }

            .inside, .woocommerce_order_items_wrapper {
                break-after: avoid;
            }

            .wooexp-body-covered td {
                width: auto;
                padding: 20px;
                min-width: 90px;
            }

            .wooexp-body-covered .thumb img {
                width: auto;
                height: auto;
                max-height: 300px;
                max-width: 300px;
            }

            .wooexp-body-covered .thumb .small-width {
                width: 150px;
            }

            .wooexp-body-covered tr {
                border: 1px solid #000;
                border-bottom: 0;
                break-inside: avoid;
            }

            .item-wrap {
                break-inside: avoid;
            }

            .wooexp-body-covered .wooexp-num-row {
                border: 1px solid #000;
                border-top: 0;
                break-after: page;
            }

            .wooexp-num-row td {
                vertical-align: middle;
            }

            .wooexp-body-covered th {
                padding: 20px;
            }

            .wooexp-customer,
            .wooexp-body-covered .woocommerce-order-data__heading,
            [data-name="order_notes_admin"],
            .wooexp-body-covered .wrap_note_item {
                margin-top: 0.5rem;
                margin-bottom: 0.5rem;
            }

            .wooexp-product-desc {
                display: block;
            }

            .wooexp-body-covered .form-field label {
                font-weight: bold;
            }

            .acf-label,
            [data-name="order_notes_admin"] .wooexp-product-desc {
                display: inline;
                font-size: 1.3em;
                font-weight: 600;
                color: #1d2327;
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

            var orderItems,
                imagesProcessed = 0,
                $body = $('body')

            /**
             * Print only specific element on a page
             */
            function printElement($itemList) {
                var targetEl = $itemList.clone();

                targetEl.removeClass()
                    .attr('id', 'woo-item-list')
                    .appendTo($body)

                if ( !targetEl.length ) {
                    return console.error('There is no such element on the page.');
                }

                orderItems = targetEl.find('#order_line_items .item');

                /** Prepare DOM elements to clone */
                const orderNotes = $('[data-name="order_notes_admin"]');
                const orderNumber = $('.woocommerce-order-data__heading');

                orderItems.each( function(i) {
                    const $this  = $(this);

                    /** Use to break HTML in order to have each product block undivided on print pages */
                    $('<tbody class="item-wrap"></tbody>')
                        .appendTo( $this.closest('.woocommerce_order_items') )
                        .append($this);
                    /**
                     * Create a row that contains item cost and quantity
                     */
                    const tr = $('<tr>');
                    tr.addClass('wooexp-num-row').appendTo($this.parent());

                    $this.find('td.item_cost').appendTo(tr);
                    $this.find('td.quantity').appendTo(tr);

                    const lineCost = $this.find('td.line_cost');
                    lineCost
                        .appendTo(tr)
                        .find('.refunded, .wc-order-item-discount').remove();

                    /** Insert Order and product number */
                    const prodName = $this.find('.wc-order-item-name')
                    const prodParams = new URLSearchParams( '?' + prodName.attr('href').split('?')[1] );
                    if (prodParams.size) {
                       $('<div></div>')
                           .html( `<strong><?php _e( 'Item number', 'wooexp' ); ?>:</strong> #<?php echo get_the_ID()?>-${prodParams.get('post')}`)
                           .insertAfter( prodName.next() )
                    }

                    /** Check if current index is the last one to trigger next step and printing */
                    const isLast = i === orderItems.length - 1;

                    /** Add order notes and order number to each page */
                    const headerTr = getHeaderTr(orderNumber, orderNotes);
                    headerTr.prependTo($this.parent());

                    /** Replace thumbnail with artwork original image and place to the right side */
                    const artwork = $this.find('.download-artwork');
                    const thumb = $this.find('.thumb')
                        .appendTo($this);
                    var imgThumb = thumb
                        .find('img');

                    if ( ! imgThumb.length ) {
                        imgThumb = $('<img>');
                        thumb.find('.wc-order-item-thumbnail').append(imgThumb);
                    }

                    if ( ! artwork.attr('href') ) {
                        ++imagesProcessed;
                        return isLast ? setPrinting(targetEl) : true;
                    }

                    const imgThumbSrc = imgThumb.attr('src');
                    var backToSrc;

                    imgThumb
                        .on('load', function() {
                            ++imagesProcessed;
                            imgThumb.removeAttr('width height');
                            backToSrc && imgThumb.addClass('small-width');
                            isLast && setPrinting(targetEl);
                        })
                        .on('error', function() {
                            if (imgThumb.attr('src') !== imgThumbSrc) { // If artwork file path is present
                                if (imgThumbSrc) {
                                    imgThumb.attr('src', imgThumbSrc);
                                    backToSrc = true;
                                } else {
                                    imgThumb.remove();
                                    ++imagesProcessed;
                                    isLast && setPrinting(targetEl);
                                }
                            } else if (backToSrc) {
                                imgThumb.remove()
                                ++imagesProcessed;
                                isLast && setPrinting(targetEl);
                            }
                        })
                        .attr('src', artwork.attr('href'));
                })
            }

            function getHeaderTr(orderNumber, orderNotes) {
                const tr = $('<tr>').addClass('wooexp-header-row');
                const td = $('<td colspan="3">');

                /**
                 * Add order notes
                 */
                switchTextAreaWithSpan( orderNotes.clone() )
                    .prependTo(td)
                    .find('.acf-label').text('Order notes: ');

                /**
                 * Add customer details to export layout
                 */
                const customer = $('.wc-customer-search').find(':selected');
                if (customer.length) {
                    const customerHtml = $(`<h3></h3>`);
                    customerHtml.addClass('wooexp-customer');
                    customerHtml.html(`Customer details: ${customer.text()}`);
                    td.prepend(customerHtml);
                }

                /**
                 * Add order number
                 */
                td.prepend( orderNumber.clone() );

                return tr.append(td);
            }

            function setPrinting(targetEl) {
                const checkImageLoad = setInterval( function() {
                    if (orderItems.length === imagesProcessed) {
                        clearInterval(checkImageLoad);
                        startPrinting(targetEl)
                    }
                }, 100);
            }

            function startPrinting(targetEl) {
                /**
                 * Clear layout to leave only product data in product section
                 */
                targetEl
                    .find('.postbox-header, thead, .button, p:not(.wrap_note_item), .wc-order-refund-items, #order_line_items')
                    .remove();

                targetEl.find('.wc-order-totals-items, #order_shipping_line_items, #order_fee_line_items, #order_refunds, .wc-order-add-item, .wc-order-bulk-actions, script')
                    .remove();

                /**
                 * Add item notes
                 */
                targetEl.find('.wrap_note_item').each( function() {
                    switchTextAreaWithSpan( $(this) );
                });

                /**
                 * Attach styles to cloned element
                 */
                targetEl.prepend($('#wooexp-style'));

                /**
                 * Return original HTML contents after successful printing
                 */
                $(window).on('afterprint', () => {
                    location.reload();
                })

                /**
                 * Print order items list only
                 */
                document.body.classList.add('wooexp-body-covered');
                try {
                    $body.children().not('#woo-item-list').remove();
                } catch (error) {
                    // TODO: dispose of it
                    document.querySelector('.mce-panel').remove()
                }

                window.print();
            }

            function switchTextAreaWithSpan(el) {
                if (el.length) {
                    const textArea = el.find('textarea');
                    const newDetails = $('<span></span>');
                    newDetails
                        .addClass('wooexp-product-desc')
                        .text(textArea.val())
                        .appendTo(el);

                    textArea.remove();
                }

                return el;
            }
        });

    } )();
    </script>
    <?php
}