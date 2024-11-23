<?php
/**
 * Function that returns HTML layout for WooCommerce Export plugin
 * to be output at WooCommerce Order Edit Screen
 * @return
 */
function get_order_export_layout() {
    $out = <<<END
    <script>
        ( () => {
            function printElement(divName) {   
                let specific_element = document.getElementById(divName).innerHTML;
                let original_elements = document.body.innerHTML;
            
                document.body.innerHTML = specific_element;
                window.print();
                document.body.innerHTML = original_elements;
            }

        } )());
    </script>
    END:
}