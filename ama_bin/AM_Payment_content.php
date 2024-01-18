<?php

class AMA_Payment_Page_Content {
    private $title;
    private $description;

    public function __construct($title, $description) {
        $this->title = $title;
        $this->description = $description;
    }

    public function get_content_html() {
        $msisdn = array(
            'type' => 'tel',
            'name' => 'msisdn',
            'min' => '0',
            'step' => '1',
            'required' => true
        );
        $submitButton = 'Do Payment';
        $msisdnLabel = 'MSISDN';
        $msisdnPlaceholder = '123456789';

        $content_html = '<h1>Payment over ' . $this->title . '</h1>
                         <p> You will do a payment of' . $this->description . '</p>
                         <form method="post">
                             <label for="' . $msisdn['name'] . '">' . $msisdnLabel . '</label>
                             <input type="' . $msisdn['type'] . '" name="' . $msisdn['name'] . '" min="' . $msisdn['min'] . '" step="' . $msisdn['step'] . '" placeholder="' . $msisdnPlaceholder . '" ' . ($msisdn['required'] ? 'required' : '') . '>
                             <button type="submit">' . $submitButton . '</button>
                         </form>';

        return $content_html;
    }
}