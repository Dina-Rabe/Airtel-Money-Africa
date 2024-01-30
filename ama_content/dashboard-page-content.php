<?php
require_once plugin_dir_path( __FILE__ ) . 'Models.php';

$account_management = new AMA_Account();
$total_transaction = $account_management->getTotalTransactionCount();


?>
<div class="loading-overlay" id="loadingOverlay">
    <div class="loading-text">Loading...</div>
    <div class="loading-spinner"></div>
</div>
<div class="ama-grid-item">
    <div class="ama_header">
        <H1><?php echo date('Y-m-d H:i:s'); ?></H1>
        <h1 class="ama_balance"><?php echo number_format($account_management->balance, 2, '.', ' ') . ' ' . $account_management->currency; ?></h1>
        <H1><?php echo number_format($account_management->getTotalTransactionCount(), 0, '.', ' ') ?> Transactions</H1>
    </div>
    <div class="ama_graph">
        <div class="ama_graph_content">
            <H3 class="ama_label">Transaction Summary</H3>
            <canvas id="ama_pieChart_summary"></canvas>
        </div>
    </div>
</div>
<div class="ama-grid-item">
    <div class="ama_table">
        <div class="ama_header">
            <H1 id="ama_search_result">List of last 50 Transactions</H1>
        </div>
        <table id="ama_transaction_table" class="ama_transactionTable">
            <thead>
                <tr>
                    <th>MSISDN</th>
                    <th>Amount</th>
                    <th>Reference</th>
                    <th>Internal ID</th>
                    <th>AM ID</th>
                    <th>Status</th>
                    <th>Response Code</th>
                    <th>Base URL</th>
                    <th>Transaction Date</th>
                </tr>
            </thead>
        </table>
    </div>
</div>
<div class="ama-grid-item">
    <div class="ama_search">
        <div class="ama_header">
            <H1 id="ama_search_result">Search items</H1>
        </div>
        <form class="ama_form">
            <div class="ama_header">
                <input type="text" id="ama_search_input" class="ama_input">
                <button type="button" class="ama_button" onclick="ama_search_items()">Search</button>
            </div>
        </form>
    </div>
    <div class="ama_table">
        <table id="ama_transactions_found" class="ama_transactionTable">
            
        </table>
    </div>
</div>

