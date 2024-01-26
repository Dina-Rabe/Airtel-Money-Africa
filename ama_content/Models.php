<?php

class AMA_Options{
    public $base_url;
    public $client_id;
    public $client_secret;
    public $country_configured;
    public $currency_configured;
    public $email_configured;

    public function __construct(){
        $this->base_url = get_option('ama_switch_mode');
        $this->client_id = get_option('ama_client_id');
        $this->client_secret = get_option('ama_client_secret');
        $this->country_configured = get_option('ama_country');
        $this->currency_configured = get_option('ama_currency');
        $this->email_configured = get_option('ama_email');
    }

    public function get_ama_token(){
        $api_url = $this->base_url . 'auth/oauth2/token';
        
        $headers = array(
            'Content-Type' => 'application/json',
            'Accept' => '*/*',
        );
        
        $body = array(
            'client_id' => $this->client_id,
            'client_secret' => $this->client_secret,
            'grant_type' => 'client_credentials',
        );
        
        $response = wp_remote_post( $api_url, array(
            'headers' => $headers,
            'body' => json_encode( $body ),
        ) );
        if ( ! is_wp_error( $response ) && wp_remote_retrieve_response_code( $response ) === 200 ) {
            $response_body = wp_remote_retrieve_body( $response );
            
            return json_decode($response_body);
        } else {
            
            return 'ERROR';
        }
    }

}

class AMA_Kyc{

    public $msisdn;
    public $isBarred;
    public $grade;
    public $lastName;
    public $firstName;
    public $isPinSet;
    public $error_message;
    
    public function __construct($msisdn_param){
        if ($msisdn_param !== null && isset($msisdn_param['msisdn'])) {
            $this->msisdn = $msisdn_param['msisdn'];
        } else {
            $this->msisdn = null;
        }
        $kyc_info_resp= $this->get_kyc_info();
        $kyc_body = (array)json_decode($kyc_info_resp['body']);

        if (isset($kyc_body['data'])) {
            $kyc_data = (array)$kyc_body['data'];
            $this->isBarred = $kyc_data['is_bared'];
            $this->grade = $kyc_data['grade'];
            $this->lastName = $kyc_data['last_name'];
            $this->firstName = $kyc_data['first_name'];
            $this->isPinSet = $kyc_data['is_pin_set'];
            
            $kyc_status = (array)$kyc_body['status'];
            $this->error_message = $kyc_status['message'];
        }else{
            $kyc_status = (array)$kyc_body['status'];
            $this->error_message = $kyc_status['message'];
        }
    }

    private function get_kyc_info(){
        $main_option = new AMA_Options();
        $api_url = $main_option->base_url . 'standard/v1/users/' . $this->msisdn;
        $token = $main_option->get_ama_token();
        $token_str = $token->access_token;
        
        $headers = array(
            'Accept' => '*/*',
            'Content-Type' => 'application/json',
            'X-Country' => $main_option->country_configured,
            'X-Currency' => $main_option->currency_configured,
            'Authorization' => 'Bearer '. $token_str
        );
        $args = array(
            'headers' => $headers
        );

        $response = wp_remote_request($api_url, $args);

        if (is_wp_error($response)) {
            $error_message = $response->get_error_message();
            return json_encode($error_message);
        } else {
            $response_code = wp_remote_retrieve_response_code($response);
            $response_body = wp_remote_retrieve_body($response);
            return array(
                "body" => $response_body,
                "status_code" => $response_code,
            );
        }        
    }
}

class AMA_Payment{

    public $msisdn;
    public $amount;
    public $reference;
    public $internal_id;
    public $am_id;
    public $message;
    public $status;
    public $response_code;
    public $code;
    public $success;

    public $transaction_date;
    public $transaction_type;
    public $base_url;

    public function __construct($params){
        
        if (isset($params["msisdn"])) {
            $this->msisdn = $params["msisdn"];
        }
        
        if (isset($params["amount"])) {
            $this->amount = $params["amount"];
            
        }
        
        if (isset($params["reference"])) {
            $this->reference = $params["reference"];
            
        }

        if (isset($params['internal_id'])) {
            $db_result = $this->fetch_ama_payment_instance($params['internal_id']);
            if ($this->msisdn == null){$this->msisdn = $db_result['msisdn']; }
            if ($this->amount == null) {$this->amount = intval($db_result['amount']); }
            if ($this->reference == null) {$this->reference = $db_result['reference'];}
            if ($this->transaction_date == null) {$this->transaction_date = $db_result['transaction_date']; }
            $this->internal_id = $params['internal_id'];
        }else{
            $this->transaction_type = 'init_command';
            $this->internal_id = str_replace(' ','-',$this->reference) . '-' . $this->msisdn . '-' . $this->amount . '-' . time();
            $this->transaction_date = date('Y-m-d H:i:s');
            $this->store_ama_payment_instance();
        }

        if (isset($params["am_id"])) {
            $this->am_id = $params["am_id"];
        }
        
        if (isset($params["status"])) {
            $this->status = $params["status"];
        }
        
        if (isset($params["response_code"])) {
            $this->response_code = $params["response_code"];
            $responseCodes = get_option("ama_response_codes");
            $this->message = $responseCodes[$this->response_code];
        }
        
        if (isset($params["base_url"])) {
            $this->base_url = $params["base_url"];            
        }

        if (isset($params["transaction_date"])){
            $this->transaction_date = $params["transaction_date"];
        }
        
    }

    public function do_payment(){
        $this->transaction_type = 'init_payment';
        $main_option = new AMA_Options();
        $token = $main_option->get_ama_token();
        $token_str = $token->access_token;
        $reference_temp = $this->reference ;
        $country = $main_option->country_configured;
        $currency = $main_option->currency_configured;
        $msisdn = $this->msisdn;
        $amount = $this->amount;
        $id = $this->internal_id;
        
        if ($token == 'ERROR'){
            $this->message = 'ERROR TOKEN';
            return false;
        }

        $url = $main_option->base_url . 'merchant/v1/payments/';
        $headers = array(
            'Content-Type' => 'application/json',
            'Accept' => '*/*',
            'X-Country' => $country,
            'X-Currency' => $currency,
            'Authorization' => 'bearer ' . $token_str
        );
        
        $data = array(
            "reference" => $reference_temp,
            "subscriber" => array(
                "country" => $country,
                "currency" => $currency,
                "msisdn" => $msisdn
            ),
            "transaction" => array(
                "amount" => $amount,
                "country" => $country,
                "currency" => $currency,
                "id" => $id
            )
        );
        $args = array(
            'headers' => $headers,
            'body' => json_encode($data),
        );
        
        $response = wp_remote_post($url, $args);
        
        if (is_wp_error($response)) {
            $this->message = $response->get_error_message();
            $this->transaction_date = date('Y-m-d H:i:s');
            $this->base_url = $main_option->base_url;
            $this->store_ama_payment_instance();
            return false;
        } else {
            $response_body = (Array)wp_remote_retrieve_body($response);
            $data = json_decode($response_body[0], true);
            
            $this->internal_id = $data['data']['transaction']['id'];
            $this->status = $data['data']['transaction']['status'];
            $this->response_code = $data['status']['response_code'];
            $this->code = $data['status']['code'];
            $this->success = $data['status']['success'];
            $this->message = $data['status']['message'];
            $this->transaction_date = date('Y-m-d H:i:s');
            $this->base_url = $main_option->base_url;
            $this->store_ama_payment_instance();
            return true;
        }
    }

    public function check_transaction_status() {
        $this->transaction_type = 'check_status';
        $main_option = new AMA_Options();
        $token = $main_option->get_ama_token();
        $token_str = $token->access_token;
        $url = $main_option->base_url . 'standard/v1/payments/' . $this->internal_id;

        // Request arguments
        $args = array(
            'headers' => array(
                'Content-Type' => 'application/json',
                'Accept' => '*/*',
                'X-Country' => $main_option->country_configured,
                'X-Currency' => $main_option->currency_configured,
                'Authorization' => 'bearer ' . $token_str,
            ),
            // 'sslverify' => false
        );

        // Make the request
        $response = wp_remote_get($url, $args);

        // Check for errors
        if (is_wp_error($response)) {
            $this->message = 'HTTP Request Error: ' . $response->get_error_message();
            $this->transaction_date = date('Y-m-d H:i:s');
            $this->base_url = $main_option->base_url;
            $this->store_ama_payment_instance();
            return false;
        } else {
            // Retrieve the response body
            $body = wp_remote_retrieve_body($response);

            // Decode the JSON response
            $data = json_decode($body, true);

            // Extract values from the response
            $this->am_id = $data['data']['transaction']['airtel_money_id'];
            $this->status = $data['data']['transaction']['status'];
            $this->response_code = $data['status']['response_code'];
            $this->code = $data['status']['code'];
            $this->success = $data['status']['success'];
            $this->transaction_date = date('Y-m-d H:i:s');
            $this->base_url = $main_option->base_url;
            $this->store_ama_payment_instance();
            $responseCodes = get_option("ama_response_codes");
            $this->message = $responseCodes[$this->response_code];
            $this->message .= ' '. $data['data']['transaction']['message'];
            

            if($this->response_code == 'DP00800001001'){
                $this->send_email_notification();
            }
            return true;            
        }
    }

    private function store_ama_payment_instance() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'ama_payments';
    
        $wpdb->insert(
            $table_name,
            array(
                'msisdn' => $this->msisdn,
                'amount' => $this->amount,
                'reference' => $this->reference,
                'internal_id' => $this->internal_id,
                'am_id' => $this->am_id,
                'message' => $this->message,
                'status' => $this->status,
                'response_code' => $this->response_code,
                'code' => $this->code,
                'success' => $this->success,
                'transaction_date' => $this->transaction_date,
                'transaction_type' => $this->transaction_type,
                'base_url' => $this->base_url,
            )
        );
    }

    private function fetch_ama_payment_instance($internal_id) {
        global $wpdb;
    
        $table_name = $wpdb->prefix . 'ama_payments';
    
        $sql = "SELECT msisdn, amount, reference, transaction_date FROM $table_name 
                WHERE internal_id = %s
                AND amount IS NOT NULL
                AND msisdn IS NOT NULL
                AND reference IS NOT NULL
                ORDER BY transaction_date DESC";
    
        $prepared_sql = $wpdb->prepare($sql, $internal_id);
        $result = $wpdb->get_row($prepared_sql, ARRAY_A);
        return $result;
    }

    private function send_email_notification() {
        $recipient_email = get_option('ama_email');
    
        if (empty($recipient_email)) {
            return "Admin email not set in settup option!";
        }
    
        $subject = 'Payment Notification for ' . $this->internal_id;
        ob_start();
        var_dump($this);
        $class_dump = ob_get_clean();

        $headers = array(
            'Content-Type: text/html; charset=UTF-8',
        );

        $message = '<html><body>';
        $message .= '<H1>Transaction details:</H1>';
        // Format class dump as a grid
        $message .= '<table style="border-collapse: collapse;">';
        $message .= '<tr><th>Property</th><th>Value</th></tr>';

        foreach ($this as $property => $value) {
            $message .= '<tr>';
            $message .= '<td style="border: 1px solid black; padding: 5px;">' . $property . '</td>';
            $message .= '<td style="border: 1px solid black; padding: 5px;">' . $value . '</td>';
            $message .= '</tr>';
        }

        $message .= '</table>';
        $message .= '<div class="email-signature">
                        <p>Solution powered by: <strong>Dina Rabenarimanitra</strong></p>
                        <p>GitHub Link: <a href="https://github.com/Dina-Rabe">https://github.com/Dina-Rabe</a></p>
                        <p>LinkedIn Link: <a href="https://www.linkedin.com/in/dina-rabenarimanitra-91aa0261/">https://www.linkedin.com/in/dina-rabenarimanitra-91aa0261/</a></p>
                    </div>';
        $message .= '</body></html>';

        // Send the email
        wp_mail($recipient_email, $subject, $message, $headers);

    }
}

class AMA_Transactions{
    public $list_AMA_Payment =[];

    public function __construct($params){
        if (isset($params['msisdn'])){
            $db_result = $this->fetch_views_data_by_msisdn($params['msisdn']);
            foreach ($db_result as $row) {
                $msisdn = $row['msisdn'];
                $amount = $row['amount'];
                $reference = $row['reference'];
                $internalId = $row['internal_id'];
                $amId = $row['am_id'];
                $status = $row['status'];
                $responseCode = $row['response_code'];
                $baseUrl = $row['base_url'];
                $transactionDate = $row['transaction_date'];
                
                $temp_ama = new AMA_Payment(
                    array(
                        'msisdn'=> $msisdn,
                        'amount'=> $amount,
                        'reference'=> $reference,
                        'internal_id'=> $internalId,
                        'am_id'=> $amId,
                        'status'=> $status,
                        'response_code'=> $responseCode,
                        'base_url'=> $baseUrl,
                        'transaction_date' => $transactionDate,

                    )
                );
                $this->list_AMA_Payment[] = $temp_ama;
            }
        }
        if (isset($params['internal_id'])){
            $temp_ama = new AMA_Payment(Array(
                'internal_id'=> $params['internal_id'],
            ));
            $isTrue = $temp_ama->check_transaction_status();
            $this->list_AMA_Payment[] = $temp_ama;
        }
    }

    private function fetch_views_data_by_msisdn($msisdn_param){
        global $wpdb;
    
        $tablePrefix = $wpdb->prefix;
        
        $query = "
            SELECT
                msisdn,
                amount,
                reference,
                internal_id,
                am_id,
                status,
                response_code,
                base_url,
                MIN(transaction_date) as transaction_date
            FROM (
                SELECT
                    msisdn,
                    amount,
                    reference,
                    internal_id,
                    am_id,
                    status,
                    response_code,
                    base_url,
                    transaction_date
                FROM {$tablePrefix}ama_failed_transaction_view
                
                UNION
                
                SELECT
                    msisdn,
                    amount,
                    reference,
                    internal_id,
                    am_id,
                    status,
                    response_code,
                    base_url,
                    transaction_date
                FROM {$tablePrefix}ama_in_progress_transaction_before_today
                
                UNION
                
                SELECT
                    msisdn,
                    amount,
                    reference,
                    internal_id,
                    am_id,
                    status,
                    response_code,
                    base_url,
                    transaction_date
                FROM {$tablePrefix}ama_in_progress_transaction_today
                
                UNION
                
                SELECT
                    msisdn,
                    amount,
                    reference,
                    internal_id,
                    am_id,
                    status,
                    response_code,
                    base_url,
                    transaction_date
                FROM {$tablePrefix}ama_success_transaction_view
            ) AS temp_table
            WHERE temp_table.msisdn = %s
            GROUP BY
                msisdn,
                amount,
                reference,
                internal_id,
                am_id,
                status,
                response_code,
                base_url;
        ";
        
        $query = $wpdb->prepare($query, $msisdn_param);
        $results = $wpdb->get_results($query, ARRAY_A);
        
        return $results;
    }
}

